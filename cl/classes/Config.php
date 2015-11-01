<?php
/**
 * 設定ファイルの読み込み・設定の取得を行います。
 *
 * 設定ファイルは以下の用例の"dbconfig.php"のように、
 * phpコード開始後、すぐに配列をreturnする形式のものでなければいけません。
 *
 * Example:
 *      //-- ./dbconfig.php
 *      <?php
 *          return array(
 *              'host'  => 'localhost',
 *              'user'  => 'userid',
 *              'pass'  => 'password
 *          );
 *
 *      //-- ./app.php
 *      <?php
 *          require 'bs.php'; // CodeLapseライブラリの 'bs.php'を読み込む
 *
 *          // 'db'名前空間に設定ファイル(dbconfig.php)を読み込む
 *          Config::load(dirname(__FILE__) . 'dbconfig.php', 'db');
 *
 *          // データベースへログイン
 *          $host = Config::get('db.host');
 *          $user = Config::get('db.user');
 *          $pass = Config::get('db.pass');
 *          $con = mysqli_connect($host, $user, $pass);
 *      ?>
 *
 * @package CodeLapse
 */
class CL_Config
{
    /**
     * @var array 読み込まれた設定データ
     */
    private static $config = array();

    /**
     * @var array(string) 設定ファイルの探索先フォルダ
     */
    private static $paths = array();

    /**
     * 設定ファイルの探索先パスを追加します。
     * Config::set メソッドで実行時、指定された名前空間が存在しない場合
     * 登録された探索先パスを探します。
     *
     * @param string $path 探索先パス
     */
    public static function addLoadPath($path)
    {
        if (in_array($path, self::$paths) === false) {
            is_dir($path) and self::$paths[] = $path . DS;
        }
    }

    /**
     * 設定ファイルを読み込みます。
     *
     * @param string $path 設定ファイルのパス
     * @param string|null $namespace 設定を読み込む空間名。
     *  省略された時、ファイル名を名前空間として指定します。
     * @param boolean|null $merge (optional) すでに名前空間に設定が読み込まれていた時、
     *  既存の設定と上書き統合するか指定します。
     *  falseが指定され、名前空間競合が発生した時は、例外が投げられます。
     *  初期値はtrueです。
     */
    public static function load($path, $namespace = null, $merge = true)
    {
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                self::load($k, $v);
            }
        }

        if (file_exists($path) === false) {
            // 読み込み先ファイルが存在しなければエラー
            throw new OutOfBoundsException('設定ファイルが存在しません。(' . $path . ')');
        }

        if (is_string($namespace) === false) {
            // 名前空間が指定されていなければ
            // ファイル名を取得する
            $namespace = pathinfo($path, PATHINFO_FILENAME);
        }

        // 設定ファイルを読み込み
        $conf = @include $path;

        if ($conf !== false) {

            if (isset(self::$config[$namespace])) {
                if ($merge === true) {
                    self::$config[$namespace] =
                        array_merge_recursive(self::$config[$namespace], $conf);
                } else {
                    throw new Exception('名前空間が競合しました: ' . $namespace);
                }
            } else {
                CL_Arr::set(self::$config, $namespace, $conf);
            }
        } else {
            throw new Exception('設定ファイルが読み込めません。(' . $path .')');
        }
    }

    /**
     * 指定された設定を取得します。
     *
     * @param string|array $key 取得したい設定名
     * @param mixed $default 値が取得できなかった時の初期値
     */
    public static function get($key = null, $default = null)
    {
        $namespace = array();

        if ($key === null) {
            return self::$config;
        }

        //-- $key から名前空間を抽出する
        if (is_array($key)) {
            foreach ($key as $k) {
                $k = explode('.', $k);
                $namespace[] = $k[0];
            }
        } else {
            $k = explode('.', $key);
            $namespace[] = $k[0];
        }

        //-- 名前空間に設定が読み込まれているかチェックする
        foreach ($namespace as $ns) {
            if (isset(self::$config[$ns]) === false) {

                // 設定が読み込まれていなければ、ファイルを探して読み込む
                foreach (self::$paths as $path) {
                    $path = $path . $ns . '.php';
                    file_exists($path) and self::load($path, $ns);
                }
            }
        }

        return CL_Arr::get(self::$config, $key, $default);
    }
}
