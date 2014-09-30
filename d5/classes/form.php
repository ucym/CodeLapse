<?php
/**
 * フォームの生成とフォームへの値の復元を行うクラスです。
 *
 * ``` php
 * <?php
 *
 * ```
 */
class D5_Form
{
    const DEFAULT_FORM_NAME = 'default';

    /*
    const CREATE = 1;
    const IGNORE = 2;
    const STRICT = 3;
    */

    private static $_values = array();


    /**
     * フィールド名をHTML属性に変換します。
     *
     * フィールド名にはCSSのセレクタ記法を使って要素のIDとクラスを指定できます。
     * 書き方は以下のとおりです。
     *
     * ```
     * name #id.class.class2
     * ```
     * 最初にname属性に設定する名前を指定し、その後ろに""半角スペースを一つ空けて""
     * #IDや.classを指定します。
     *
     * @param string $name フィールド名
     * @return array nameキーを含む配列
     */
    private static function parseName($name)
    {
        $attr = array();

        // nameの取り出し
        preg_match('/^([\w-]+?)(?: |$)/s', $name, $matched_name);
        isset($matched_name[1]) and $attr['name'] = $matched_name[1];

        // IDの取り出し
        // ex: Hit-> #name, #name_input-hoge; No Hit-> #-a, #0aa...
        preg_match('/#([a-zA-Z](?:[\w-]?)+?)(?:[\.#]|$)/', $name, $matched_id);
        isset($matched_id[1]) and $attr['id'] = $matched_id[1];

        // クラスの取り出し
        preg_match_all('/\.([a-zA-Z](?:[\w-]?)+)/', $name, $matched_classes);
        isset($matched_classes[1]) and $attr['class'] = implode(' ', $matched_classes[1]);

        if (! isset($attr['name'])) {
            throw new Exception('D5_Form フィールド名構文エラー、 フィールド名の直後に半角スペースがあるか確認して下さい。');
        }

        return $attr;
    }


    /**
     * HTML属性文字列をパースし、配列に変換します。
     *
     * HTML属性文字列とは "attr='b' attr2='b'"のような、
     * HTMLの属性だけの文字列です。
     *
     * @param string $attrStr
     */
    private static function parseHTMLAttr($attrStr)
    {
        if (is_array($attrStr)) {
            return $attrStr;
        }

        $attr = array();
        $str = preg_split("//u", $attrStr, -1, PREG_SPLIT_NO_EMPTY);
        $chars = count($str);

        $buf = array('');
        $key = null;
        $val = null;
        $quote = null;

        for ($i = 0; $i < $chars; $i++) {
            $c = $str[$i];

            switch ($c) {
                case '=':
                    if ($quote === null) {
                        $key = implode('', $buf);
                        $buf = array('');
                    }

                    break;

                case '\'':
                case '"' :
                    if ($quote === null) {
                        $quote = $c;
                    }
                    else if ($quote === $c) {
                        $val = implode('', $buf);
                        $buf = array('');
                        $quote = null;
                    }
                    else {
                        $buf[] = $c;
                    }

                    break;

                default:
                    $buf[] = $c;
            }

            if ($key !== null and $val !== null) {
                $attr[$key] = $val;
                $key = null;
                $val = null;

                // スペースをチェック
                if (++$i < $chars and $str[$i] !== ' ') {
                    throw new Exception('D5_Form HTML属性構文エラー');
                }
            }
        }

        return $attr;
    }


    /**
     * 配列を一つにマージします。
     *
     * array_merge関数と違うのは、このメソッドが破壊的であるということです。
     * このメソッドは最初の引数（マージ先）の参照を受け取り、
     * その配列内を直接書き換えます。
     *
     * @param array &$mergeIn   配列のマージ先
     * @param array ...$arr     マージする配列（複数）
     */
    private static function mergeByRef(array & $mergeIn)
    {
        $args = func_get_args();
        array_shift($args);

        foreach ($args as $arr) {
            if (! is_array($arr)) {
                continue;
            }

            foreach ($arr as $k => $v) {
                $mergeIn[$k] = $v;
            }
        }
    }


    /**
     * HTMLタグを生成します。
     *
     * @param string    $tag        タグ名
     * @param string    $inner      （省略可）タグの内部コンテンツ
     * @param boolean   $close      （省略可）閉じタグをつけるか
     * @param array     $attributes （省略可）設定する属性。 "属性名" => "値"の連想配列
     * @return string HTML
     */
    private static function buildHTML($tag, $inner = null, $close = true, array $attributes = array())
    {
        $buf = array('<');
        $buf[] = $tag;

        $attributes = array_reverse($attributes, true);

        foreach ($attributes as $attr => $value) {
            $buf[] = ' ';

            if (is_numeric($attr)) {
                $buf[] = $value;
                continue;
            }
            else {
                $buf[] = $attr . '=' . '"' . htmlspecialchars($value) . '"';
            }
        }

        $buf[] = $close === true ? '>' : ' />';

        $inner !== null and $buf[] = $inner;
        $close === true and $buf[] = '</' . $tag . '>';

        return implode('', $buf);
    }


    /**
     * input要素を生成します。
     *
     * @param string        $type       type属性
     * @param string        $name       name属性
     * @param array         $attr       (optional) 優先的に設定する属性
     * @param string|array  $userAttr   (optional) ユーザー定義の属性（上書きされます）
     * @param string        $form       (optional) 値を取り出すグループ名
     */
    private static function buildInput(
        $type,
        $name,
        $attr = array(),
        $userAttr   = array(),
        $form       = null
    ) {
        // 引数を初期化
        $attr === null      and $attr = array();
        $userAttr === null  and $attr = array();
        $overAttr = array_merge($attr, array('type' => $type));

        // 属性値を準備
        $attributes = is_string($userAttr) ?
                            self::parseHTMLAttr($userAttr)
                            : (array) $userAttr;

        // name, id, class, value 属性を取得
        $names      = self::parseName($name);
        $value      = self::getValue($names['name'], $form);

        self::mergeByRef($overAttr, $names);
        $value !== null and $overAttr['value'] = $value;

        // 優先順位の低い属性を上書きしてマージ
        self::mergeByRef($attributes, $overAttr);

        return self::buildHTML('input', null, false, $attributes);
    }


    /**
     * フォームに初期値を割り当てます。
     *
     * 割り当てる変数は以下のような形式でなければいけません
     *
     * ```php
     * <?php
     * $values = array(
     *   'name属性の値' => 'value属性の値',
     *
     *   // checkbox, select に対応する値を示すとき
     *   // （選択された要素がひとつのみであれば、値はただの文字列でも問題ありません）
     *   'checkboxのname属性の値' => array('選択値1', '選択値2')
     * );
     * ```
     *
     * この形式に沿わない場合、フィールド生成時に正しく値が復元されません。
     *
     * @param array     $value  フォームに割り当てる初期値が入った配列
     * @param string    $form   (optional) 値を割り当て先グループ名（お好みの名前、省略可）
     */
    public static function setValue(array $value, $form = null)
    {
        ! is_string($form) and $form = D5_Form::DEFAULT_FORM_NAME;
        self::$_values[$form] = & $value;
    }


    /**
     * フォームに割り当てられた値を取得します。
     *
     * @param string $field 値を取得したいフィールドの名前
     * @param string $form  (省略可) 値を取得するグループ名
     * @return string|array|null 取得した値
     */
    public static function getValue($field, $form = null)
    {
        ! is_string($form) and $form = D5_Form::DEFAULT_FORM_NAME;
        return D5_Arr::get(self::$_values, "$form.$field");
    }


    /**
     * 不可視フィールドを生成し、出力します。
     *
     * もし、第３引数でvalue属性が指定されていても
     * 第２引数に指定したvalueが優先されます。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param string        $value  value属性に設定する値
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function hidden($name, $value, $attr = array(), $form = null)
    {
        echo self::buildInput('hidden', $name, array('value' => $value), $attr, $form);
    }


    /**
     * テキストボックスを生成し、出力します。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function text($name, $attr = array(), $form = null)
    {
        echo self::buildInput('text', $name, null, $attr, $form);
    }


    /**
     * パスワードフォームを生成し、出力します。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function password($name, $attr = array(), $form = null)
    {
        echo self::buildInput('password', $name, null, $attr, $form);
    }


    /**
     * テキストエリアを生成し、出力します。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function textarea($name, $attr = array(), $form = null)
    {
        $names  = self::parseName($name);
        $value  = self::getValue($names['name'], $form);

        $userAttr = is_string($attr) ?
                        self::parseHTMLAttr($attr)
                        : (array) $attr;
        $attr = array('name' => $names['name']);

        echo self::buildHTML('textarea', htmlspecialchars($value), true, array_merge($userAttr, $attr));
    }


    /**
     * チェックボックスを生成し、出力します。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param string        $value  value属性に設定する値
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function checkbox($name, $value, $attr = array(), $form = null)
    {
        $overrideAttr   = array('value' => $value);
        $names          = self::parseName($name);
        $values         = (array) self::getValue($names['name'], $form);

        in_array($value, $values) and $overrideAttr['checked'] = 'checked';

        echo self::buildInput('checkbox', $name, $overrideAttr, $attr, $form);
    }


    /**
     * ラジオボタンを生成し、出力します。
     *
     * @param string        $name   フィールドの名前(name属性に設定されます)
     * @param string        $value  value属性に設定する値
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form   初期値が設定されているグループ名
     */
    public static function radio($name, $value, $attr = array(), $form = null)
    {
        $overrideAttr   = array('value' => $value);
        $names          = self::parseName($name);
        $selectedValue  = self::getValue($names['name'], $form);

        $selectedValue === $value and $overrideAttr['checked'] = 'checked';

        echo self::buildInput('checkbox', $name, $overrideAttr, $attr, $form);
    }


    /**
     * セレクトボックスを生成し、出力します。
     *
     * 第２引数 $selectionは以下の形式でなければなりません。
     * ```php
     * array(
     *    '表示名' => "value属性の値",
     *    // 属性を指定する場合は、配列の最初、もしくは、'valu'eキーにvalue属性の値を指定してください。
     *    // '表示名' => array("value属性の値", '属性名' => '値', '属性名2' => '値2'),
     *     '表示名2' => "value属性の値2",
     * )
     * ```
     *
     * TODO optgroup
     *
     * @param string        $name       フィールドの名前(name属性に設定されます)
     * @param string        $selection  value属性に設定する値
     * @param array|string  $attr       （省略可）HTMLに設定する属性。
     *                                  "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                                  （属性部分の文字列 例: "min='0' max='20'"）
     * @param string        $form       初期値が設定されているグループ名
     */
    public static function select(
        $name,
        array $selection,
        // TODO $notAvailable = null,
        $attr = array(),
        $form = null
    ) {
        $buf            = array();
        $overrideAttr   = self::parseName($name);
        $values         = (array) self::getValue($overrideAttr['name'], $form);
        $userAttr       = is_string($attr) ?
                            self::parseHTMLAttr($attr)
                            : (array) $attr;

        // option要素を構築
        foreach ($selection as $viewName => $value) {

            if (is_array($value)) {
                $attr    = $value;
                $value  = null;

                if (isset($attr[0])) {
                    $value = $attr;
                    unset($attr[0]);
                }
                else if (isset($attr['value'])) {
                    $value = $attr['value'];
                    unset($attr['value']);
                }
                else {
                    throw new InvalidArgumentException("フィールド $name内に valueが設定されていない項目があります。");
                }

                $attr['value'] = $value;
                in_array($value, $values) and $attr['selected'] = 'selected';
                $buf[] = self::buildHTML('option', $viewName, true, $attr);
            }
            else {
                $attr = array('value' => $value);
                in_array($value, $values) and $attr['selected'] = 'selected';
                $buf[] = self::buildHTML('option', $viewName, true, $attr);
            }
        }

        echo self::buildHTML('select', implode('', $buf), true, array_merge($userAttr, $overrideAttr));
    }


    /**
     * ファイルセレクトフォームを生成し、出力します。
     *
     * <b>入力値の復元はされません。</b>
     *
     * @param string        $name       フィールドの名前(name属性に設定されます)
     * @param array|string  $attr       （省略可）HTMLに設定する属性。
     *                                  "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                                  （属性部分の文字列 例: "min='0' max='20'"）
     */
    public static function file($name, $attr = array())
    {
        echo self::buildInput('file', $name, null, $attr, null);
    }


    /**
     * submitボタンを生成し、出力します。
     *
     * @param string        $value  表示するテキスト
     * @param array|string  $attr   （省略可）HTMLに設定する属性。
     *                              "属性名" => "値"の連想配列、もしくはHTMLの属性部分の文字列
     *                              （属性部分の文字列 例: "min='0' max='20'"）
     */
    public static function submit($value, $attr = array())
    {
        $attr = is_string($attr) ?
                    self::parseHTMLAttr($attr)
                    : (array) $attr;
        $attr = array_merge($attr, array('type' => 'submit'));
        echo self::buildHTML('input', null, false, $attr);
    }
}
