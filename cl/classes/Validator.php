<?php
namespace CodeLapse;

use \Exception;

use CodeLapse\Arr;
use CodeLapse\Config;
use CodeLapse\Valitron\Validator as Valitron;

/**
 *
 */
class Validator
{
    const DEFAULT_FIELDSET_NAME = 'default';




    /*
    Static members
    */

    protected static $instances = array();

    protected static $customRules = array();

    public static function getInstance($name = null, $errorOnNull = false)
    {
        $name === null and $name = static::DEFAULT_FIELDSET_NAME;
        $instance = Arr::get(static::$instances, $name);

        if ($errorOnNull and $instance === null) {
            throw new OutOfRangeException('フィールドセット('.$fieldSet.')が設定されていません。');
        }

        return $instance;
    }


    /**
     * 検証ルールの定義を始めます。
     * この関数はdefineメソッドの別名です。
     * @param string|array      $fieldSet       検証ルールの配列、もしくはフィールドセット名
     * @param string?           $fieldSetName   フィールドセット名
     * @return static
     */
    public static function def($fieldSet = null, $fieldSetName = null, $config = array())
    {
        return static::define($fieldSet, $fieldSetName, $config);
    }


    /**
     * 検証ルールの定義を始めます。
     * @param string|array      $fieldSet       検証ルールの配列、もしくはフィールドセット名
     * @param string?           $fieldSetName   フィールドセット名
     * @return static
     */
    public static function define($fieldSet = null, $fieldSetName = null, $config = array())
    {
        $rules = null;

        if (is_string($fieldSet)) {
            // When arguments is (string $fieldSetName = null)
            list($fieldSetName, $fieldSet) = [$fieldSet, null];
        }
        else if (is_array($fieldSet)) {
            // When arguments is (array $rules, string $fieldSetName = null)
        }

        $fieldSetName === null and $fieldSetName = static::DEFAULT_FIELDSET_NAME;

        return static::$instances[$fieldSetName] = new static($fieldSet, $config);
    }


    /**
     * オリジナルの検証ルールを追加します。
     * @param string        $ruleName       検証ルール名
     * @param callable      $validator      検証を行う関数
     *      検証時に`($value, $fieldName, $options)`が渡されます。
     * @param callable?     $jsGenerator    クライアントサイドの検証を行うJavaScriptコードを返す関数
     *      コード生成時に`($value, $fieldName, $params)`が渡され、
     *      生成されたコードは `function (value, fieldName) {` と ` }` でラップされて出力されます。
     */
    public static function addRule(
        $ruleName,
        callable $validator,
        callable $jsGenerator = null
    ) {
        Valitron::addRule($ruleName, $validator);

        static::$customRules[$ruleName] = [
            'validator'     => $validator,
            'jsGenerator'   => $jsGenerator
        ];
    }


    /**
     * クラス、あるいはオブジェクトから検証ルールを登録します。
     *
     * 登録するクラス内の`validate`から始まるメソッドがバリデータとして登録されます。
     * 検証ルール名はメソッド名の先頭から`validate`, `validate_`を取り除いたものになります。
     *
     * 例えば、`validateMailUnused`は`MailUnused`として登録され
     * `validate_unused_userid`は`unused_userid`という名前で登録されます。
     *
     * メソッド名の先頭が`jsValidate`プレフィックスから始まり
     * プレフィックス以降の名前が一致する`validate`メソッドがある場合
     *
     * JavaScript用の検証コード生成メソッドとして登録されます。
     * 例えば、`validateMailUnused`というメソッドに対して、`jsValidateMailUnused`メソッドが存在する場合
     * クライアントサイド用の検証コードを生成する際に`jsValidateMailUnused`メソッドがコールされます。
     *
     * @param string|object     $class      完全修飾クラス名、もしくはインスタンスオブジェクト
     */
    public static function addRuleClass($class)
    {
        $methods = get_class_methods($class);

        foreach ($methods as $method) {
            if (strpos($method, 'jsValidate') === 0) continue;
            if (strpos($method, 'validate') !== 0) continue;

            $methodName = substr($method, 8);
            $ruleName = ltrim($methodName, '_');

            $jsGenMethodName = 'jsValidate' . $methodName;
            $jsGenMethod = method_exists($class, $jsGenMethodName) ? [$class, $jsGenMethodName] : null;

            static::addRule($ruleName, [$class, $method], $jsGenMethod);
        }
    }


    /**
     * 指定された名前の検証ルールが登録されているか調べます。
     * （テスト用メソッド）
     * @param string        $rule       ルール名
     * @return bool
     */
    public static function hasRule($rule)
    {
        return isset(static::$customRules[$rule]);
    }


    /**
     * 値の検証を行います。
     * @return bool
     */
    public static function check(array $data, $fieldSet = null)
    {
        if (is_string($fieldSet)) {
            $instance = static::getInstance($fieldSet, true);
        }
        else if (is_array($fieldSet)) {
            $instance = new static($fieldSet);
        }
        else {
            $instance = static::getInstance($fieldSet, true);
        }

        return $instance->execute($data);
    }


    /**
     * @param string        $fieldName
     * @param string        $fieldSet       フィールドセット名
     */
    public static function errors($fieldName = null, $fieldSet = null)
    {
        $instance = static::getInstance($fieldSet, true);
        return $instance->getErrors($fieldName);
    }


    /**
     * クライアントサイド検証用のJavaScriptコードを生成します。
     * @param bool          $wrapScript     生成されたコードをscriptタグで囲むか
     * @param string        $fieldSetName   フィールドセット名
     */
    public static function js($wrapScript = false, $fieldSetName = null)
    {
        $instance = static::getInstance($fieldSetName, true);
        // TODO
        // return $instance->buildJSValidator();
    }


    /**
     * カスタムルールとデフォルトインスタンスを初期化します。
     * （テスト用メソッド）
     */
    public static function reset()
    {
        static::$instances[static::DEFAULT_FIELDSET_NAME] = null;
        static::$customRules = array();
    }




    /*
    Dynamic members
    */

    /**
     * @var CodeLapse\Valitron\Validator
     */
    protected $validator = null;


    /**
     * @var array
     */
    protected $fieldSet = array();

    /**
     * @var array
     */
    protected $config = array();


    /**
     * @param array     $fieldSet
     */
    public function __construct(array $fieldSet = null, array $config = array())
    {
        $this->config = array_merge(Config::get('validator'), $config);

        list($lang, $langDir) = array_values(Arr::get($this->config, ['lang', 'langDir']));

        $this->validator = new Valitron(null, null, $lang, $langDir);
        is_array($fieldSet) and $this->rules($fieldSet);
    }


    public function driver()
    {
        return $this->validator;
    }


    /**
     * フィールドに対する検証ルールを設定します。
     * @param string    $fieldName      検証するフィールド名
     * @param array     $rules          検証ルールのリスト
     * @return $this
     */
    public function rule($fieldName, array $rules)
    {
        $fieldLabelPair = explode(':', $fieldName, 2);
        $field = $fieldLabelPair[0];
        $label = Arr::get($fieldLabelPair, '1', $field);

        $this->fieldSet[$field] = [
            'label'     => $label,
            'rules'     => $rules
        ];

        return $this;
    }


    /**
     * @return $this
     */
    public function rules(array $fieldSet)
    {
        foreach ($fieldSet as $field => $rules) {
            $this->rule($field, $rules);
        }

        return $this;
    }

    /**
     * 検出したエラーのメッセージを取得します。
     * @param string $fieldName     (optional) フィールド名
     * @return string|bool
     */
    public function getErrors($fieldName = null)
    {
        return $this->validator->errors($fieldName);
    }


    /**
     * 値の検証を行います。
     * @param array     $data       検査するデータ（"fieldName" => "value"形式の連想配列）
     * @return bool
     */
    public function execute(array $data)
    {
        $v = $this->validator;
        $v->reset();

        // Convert
        //  'field' => ['rule', 'rule' => params] to ['rule' => [['field'], ['field', 'params..']]
        $valitronRules = array();
        $valitronLabels = array();
        foreach ($this->fieldSet as $field => $config) {
            $rules = $config['rules'];
            $label = $config['label'];

            $valitronLabels[$field] = $label;

            foreach ($rules as $ruleName => $params) {
                if (is_int($ruleName)) {
                    $ruleName = $params;
                    $params = array();
                }

                isset($valitronRules[$ruleName]) or $valitronRules[$ruleName] = array();
                $valitronRules[$ruleName][] = array_merge((array) $field, (array) $params);
            }
        }

        $v->labels($valitronLabels);
        $v->rules($valitronRules);

        return $v->validate($data);
    }
}
