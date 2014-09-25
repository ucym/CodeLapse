<?php
/**
 * Smartyラッパークラス
 *
 * HTMLの自動エスケープや、グローバル変数の機能が追加されたSmartyのラッパークラスです。<br>
 *
 * 読み込みサンプル
 *
 * ```php
 * <?php
 * //-- app/b.php

 * define('APPPATH', dirname(__FILE__));
 * D5_Config::load(APPPATH . '/config/smarty.php', 'smarty');
 *
 * // もしくは
 * // D5_Config::addLoadPath(APPPATH . '/config/');
 *```
 *
 * ```php
 * <?php
 * //-- app/config/smarty.php
 *
 * return array(
 *     // string: テンプレートが保存されているディレクトリ
 *     'template_dir' => APPPATH . '/templates/',
 *
 *     // string: コンパイル済みテンプレートを保存するディレクトリ
 *     'compile_dir' => APPPATH . '/tmp/smarty/compiled/,
 *
 *     // string: 設定ファイルが保存されているディレクトリ
 *     'config_dir' => null,
 *
 *     // string: キャッシュファイルを保存するディレクトリ
 *     'cache_dir' => APPPATH . '/tmp/smarty/cache/,
 *
 *     // boolean: キャッシュの有効 / 無効
 *     'caching' => true,
 * );
 * ```
 *
 * ```php
 * <?php
 * //-- index.php
 *
 * $smarty = D5_Smarty::instance();
 * // *do_something*
 *```
 */
class D5_Smarty
{
    //--------
    //-- Static Property, Method
    //--------

    /**
     * @ignore
     * @var array(D5_Smarty) 生成したインスタンス
     */
    private static $instances = array();


    /**
     * @var array グローバル空間に設定された変数名と値を保持します。
     */
    private static $_globalAssignStore = array();


    /**
     * 与えられた文字列を安全なHTML文字列に変換します。
     *
     * @param  string|mixed $str エスケープする文字列。
     *                        文字列でない場合はエスケープ処理は行われず、無視されます。
     * @return mixed 安全なHTML文字列、もしくは与えられたオブジェクト
     */
    public static function escapeHelper($str)
    { // エスケープ処理時に外部からコールさせるので publicにする
        if (is_string($str)) {
            return stripcslashes(htmlspecialchars($str));
        }
        else {
            return $str;
        }
    }


    /**
     * D5_Smartyのインスタンスを取得します。
     *
     * @param string $name   (optional) 取得するインスタンスの名前。
     *                       指定されない場合、'null'を使用します。
     * @param array  $config D5_Smartyをインスタンス化するときの設定
     * @return D5_Smarty
     */
    public static function instance($name = null, $config = array())
    {
        $name = $name === null ? 'default' : $name;

        if (isset(self::$instances[$name]) === false) {
            self::$instances[$name] = new self($config);
        }

        return self::$instances[$name];
    }


    /**
     * D5_Smartyインスタンス間で共通の変数を設定します。
     *
     * @param string|array $name   変数名、もしくは、変数名 => 値の連想配列
     * @param mixed        $value  設定する値
     * @param boolean      $escape エスケープを行うかを指定します。
     */
    public static function setInGlobal($name, $value, $escape = true)
    {
        if ($escape !== false) {
            if (is_array($name)) {
                $key = D5_Arr::mapRecursive($name, array('D5_Smarty', 'escapeHelper'));
            }
            else {
                $value = self::escapeHelper($value);
            }
        }

        D5_Arr::set(self::$_globalAssignStore, $name, $value);
    }


    /**
     * D5_Smartyインスタンスで共通の変数を設定します。
     *
     * setInGlobalと違い、設定する値はエスケープされません。
     *
     * @param string|array $name  変数名、もしくは、変数名 => 値の連想配列
     * @param mixed        $value=null 設定する値
     */
    public static function setRawInGlobal($name, $value = null)
    {
        D5_Arr::set(self::$_globalAssignStore, $name, $value);
    }


    /**
     * D5_Smartyインスタンス間で共通の変数を取得します。
     *
     * @param string $name    変数名
     * @param string $default 値
     */
    public static function getInGlobal($name = null, $default = null)
    {
        return D5_Arr::get(self::$_globalAssignStore, $name, $default);
    }


    /**
     * D5_Smartyインスタンス間で共通の変数を破棄します。
     *
     * @param   string|array $name 破棄する変数名、もしくは破棄する変数名の配列
     */
    public static function clearInGlobal($name)
    {
        D5_Arr::delete(self::$_globalAssignStore, $name);
    }


    /**
     * D5_Smartyインスタンスで共通の変数をすべて破棄します。
     */
    public static function clearAllInGlobal()
    {
        self::$_globalAssignStore = array();
    }


    //--------
    //-- Dynamic Property, Method
    //--------


    /**
     * @var array smartyに割り当てられた値
     */
    private $_assign = array();


    /**
     * @var Smarty smartyインスタンス
     */
    private $_smarty;


    /**
     * @param array $config Smartyの設定
     */
    public function __construct($config = array())
    {
        $sm = new Smarty();

        // 設定ファイルを読み込む
        $conf = D5_Config::get('smarty');
        array_merge($conf, $config);

        $sm->template_dir = D5_Arr::get($conf, 'template_dir') . DS;
        $sm->compile_dir  = D5_Arr::get($conf, 'compile_dir') . DS;
        $sm->config_dir   = D5_Arr::get($conf, 'config_dir') . DS;
        $sm->cache_dir    = D5_Arr::get($conf, 'cache_dir') . DS;
        $sm->caching      = D5_Arr::get($conf, 'caching', true);

        $this->_smarty = $sm;
    }

    /**
     * Smartyに値を割り当てます。
     *
     * @param string|array $key テンプレート内の変数名
     * @param mixed $value 割り当てる値 / オブジェクト
     * @param boolean $filtering (optional) 値を自動的エスケープするか。デフォルト値はtrue
     * @return D5_Smarty 現在のインスタンス
     */
    public function set($key, $value = null, $filtering = true)
    {
        if ($filtering !== false) {
            if (is_array($key)) {
                $key = D5_Arr::mapRecursive($key, array('D5_Smarty', 'escapeHelper'));
            } else {
                $value = self::escapeHelper($value);
            }
        }

        D5_Arr::set($this->_assign, $key, $value);
        return $this;
    }


    /**
     * Smartyに値を割り当てます。
     *
     * setメソッドとは違い、割り当てた値は*エスケープされません。*
     *
     * @param string|array $key テンプレート内の変数名
     * @param mixed $value 割り当てる値 / オブジェクト
     * @param boolean $filtering (optional) 値を自動的エスケープするか。デフォルト値はtrue
     * @return D5_Smarty 現在のインスタンス
     */
    public function setRaw($key, $value = null)
    {
        D5_Arr::set($this->_assign, $key, $value);
        return $this;
    }


    /**
     * Smartyへ割り当てた値を参照します。
     *
     * 変数の参照は以下の順序で行われ、値が見つかった時点でその値が返されます。<br>
     * 1. インスタンスに割り当てられた値<br>
     * 2. D5_Smarty共通変数<br>
     * 3. 渡されたデフォルト値
     *
     * @param string|array $key     取得する変数名、もしくは取得する変数名の配列
     * @param mixed        $default 値が設定されていなかった時の初期値
     * @return mixed 取得した値か、変数名 => 値 の連想配列
     */
    public function get($key = null, $default = null)
    {
        $global = self::getGlobal($key, $default);
        return D5_Arr::get($this->_assign, $key, $global);
    }


    /**
     * Smartyへ割り当てた値を破棄します。
     *
     * @param string|array $key 破棄する変数名、もしくは、破棄する変数名の配列
     * @return D5_Smarty 現在のインスタンス
     */
    public function clear($key)
    {
        D5_Arr::delete($this->_assign, $key);
        return $this;
    }


    /**
     * Smartyへ割り当てた値をすべて破棄します。
     *
     * @return D5_Smarty 現在のインスタンス
     */
    public function clearAllAssign()
    {
        $this->_assign = array();
        return $this;
    }


    /**
     * Smartyの実行結果を取得します。
     *
     * @param string      $template   使用するテンプレート
     * @param string|null $cache_id   (optional) キャッシュID
     * @param string|null $compile_id (optional) コンパイルファイルID
     * @returns string テンプレートをコンパイル結果
     */
    public function fetch($template, $cache_id = null, $compile_id = null)
    {
        // Smartyの変数割り当てをすべて破棄
        $this->_smarty->clearAllAssign();

        // 再割り当て
        $assign = array_merge(self::getInGlobal(), $this->_assign);
        foreach ($assign as $k => & $v) {
            $this->_smarty->assignByRef($k, $v);
        }

        return $this->_smarty->fetch($template, $cache_id, $compile_id);
    }


    /**
     * Smartyの実行結果を出力（表示）します。
     *
     * @param string      $template   使用するテンプレート
     * @param string|null $cache_id   (optional) キャッシュID
     * @param string|null $compile_id (optional) コンパイルファイルID
     */
    public function display($template, $cache_id = null, $compile_id = null)
    {
        echo $this->fetch($template, $cache_id, $compile_id);
    }
}
