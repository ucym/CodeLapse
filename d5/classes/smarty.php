<?php
/**
 * D5による Smartyラッパークラス
 *
 * HTMLの自動エスケープなどの機能が追加されたSmartyのラッパークラスです。<br>
 * このクラスを使用する前に、
 * D5_Config::loadメソッドで、smartyの設定を読み込むことを推奨します。<br>
 * そのようにすることで、プロジェクトで一貫した設定を使い回すことができるためです。
 *
 * 読み込みサンプル
 *      //-- app/b.php
 *      define('APPPATH', dirname(__FILE__));
 *
 *      Config::load(APPPATH . '/config/smarty.php', 'smarty');
 *      // もしくは
 *      Config::addLoadPath(APPPATH . '/config/');
 *
 *
 *      //-- app/config/smarty.php
 *      <?php
 *          return array(
 *              // string: テンプレートが保存されているディレクトリ
 *              'template_dir' => APPPATH . '/templates/',
 *
 *              // string: コンパイル済みテンプレートを保存するディレクトリ
 *              'compile_dir' => APPPATH . '/tmp/smarty/compiled/,
 *
 *              // string: 設定ファイルが保存されているディレクトリ
 *              'config_dir' => null,
 *
 *              // string: キャッシュファイルを保存するディレクトリ
 *              'cache_dir' => APPPATH . '/tmp/smarty/cache/,
 *
 *              // boolean: キャッシュの有効 / 無効
 *              'caching' => true,
 *          );
 *
 *
 *      //-- somefile.php
 *      $smarty = D5_Smarty::instance();
 *      $smarty-> *do_something*
 *
 * @package D5
 */
class D5_Smarty
{
    /**
     * @ignore
     * @var array(D5_Smarty) 生成したインスタンス
     */
    private static $instances = array();


    /**
     * 与えられた文字列を安全なHTML文字列に変換します。
     *
     * @ignore
     * @param string $str
     */
    public static function toSafeString($str)
    {
        if (is_string($str)) {
            return D5_Security::safeHtml($str, true);
        } else {
            return $str;
        }
    }

    /**
     * D5_Smartyのインスタンスを取得します。
     *
     * @param string $name (optional) 取得するインスタンスの名前。
     *   指定されない場合、'null'を使用します。
     * @param array $config D5_Smartyをインスタンス化するときの設定
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
     * Smartyのdefaultインスタンスに値を割り当てます。
     * 文字列は自動的にHTMLエスケープされます。
     *
     * @param string|array $key テンプレート内の変数名
     * @param mixed $value 割り当てる値 / オブジェクト
     * @param boolean (optional) 値を自動的エスケープするか。デフォルト値はtrue
     * @return D5_Smarty
     */
    /*
    public static function set($key, $value = null, $filtering = true)
    {
        return self::instance()->set($key, $value, $filtering);
    }
    */

    /**
     * Smartyのdefaultインスタンスへ割り当てた値を参照します。
     *
     * @param string キー名
     * @param mixed|null $default 値が設定されていなかった時の初期値
     */
    /*
    public static function get($key, $default = null)
    {
        return self::instance()->set($key, $default);
    }
    */


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

        $sm->template_dir = Arr::get($conf, 'template_dir') . DS;
        $sm->compile_dir  = Arr::get($conf, 'compile_dir') . DS;
        $sm->config_dir   = Arr::get($conf, 'config_dir') . DS;
        $sm->cache_dir    = Arr::get($conf, 'cache_dir') . DS;
        $sm->caching      = Arr::get($conf, 'caching', true);

        $this->_smarty = $sm;
    }


    /**
     * Smartyの実行結果を取得します。
     *
     * @param string $template 使用するテンプレートへのパス。
     * @param string|null $cache_id (optional) キャッシュID
     * @param string|null $compile_id (optional) コンパイルファイルID
     */
    public function fetch($template, $cache_id = null, $compile_id = null)
    {
        // 変数割り当てを一旦解除
        $this->_smarty->clearAllAssign();

        // 再割り当て
        foreach (array_keys($this->_assign) as $k) {
            $this->_smarty->assignByRef($k, $this->_assign[$k]);
        }

        return $this->_smarty->fetch($template, $cache_id, $compile_id);
    }


    /**
     * Smartyの実行結果を出力（表示）します。
     *
     * @param string $template 使用するテンプレートへのパス。
     * @param string|null $cache_id (optional) キャッシュID
     * @param string|null $compile_id (optional) コンパイルファイルID
     */
    public function display($template, $cache_id = null, $compile_id = null)
    {
        echo $this->fetch($template, $cache_id, $compile_id);
    }


    /**
     * @ignore
     * @return D5_Smarty
     */
    public function assign($key, $value = null, $filtering = true)
    {
        return $this->set($key, $value, $filtering);
    }


    /**
     * @ignore
     * @return D5_Smarty
     */
    public function assignByRef($key, & $value)
    {
        return $this->set($key, $value, false);
    }


    /**
     * @ignore
     * @return D5_Smarty
     */
    public function clear_all_assign()
    {
        unset($this->_assign);
        $this->_assign = array();

        $this->_smarty->clearAllAssign();

        return $this;
    }

    /**
     * Smartyに値を割り当てます。
     * 文字列は自動的にHTMLエスケープされます。
     *
     * @param string|array $key テンプレート内の変数名
     * @param mixed $value 割り当てる値 / オブジェクト
     * @param boolean $filtering (optional) 値を自動的エスケープするか。デフォルト値はtrue
     * @return D5_Smarty
     */
    public function set($key, $value = null, $filtering = true)
    {
        if ($filtering !== false) {
            if (is_array($key)) {
                $key = D5_Arr::mapRecursive($key, array('D5_Smarty', 'toSafeString'));
            } else {
                $value = self::toSafeString($value);
            }
        }

        Arr::set($this->_assign, $key, $value);
        return $this;
    }

    /**
     * Smartyへ割り当てた値を参照します。
     *
     * @param string|null キー名
     * @param mixed|null $default 値が設定されていなかった時の初期値
     */
    public function get($key = null, $default = null)
    {
        return Arr::get($this->_assign, $key, $default);
    }
}