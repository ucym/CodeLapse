<?php
/**
 * 設定ファイルの読み込み / 設定の取得を行います。
 * 
 * Example:
 *      //-- /dbconfig.php
 *      <?php
 *          return array(
 *              'host'  => 'localhost',
 *              'user'  => 'userid',
 *              'pass'  => 'password
 *          );
 * 
 *      //-- /app.php
 *      <?php
 *          require 'bootstrap.php'; // Rooseライブラリの 'bootstrap.php'を読み込む
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
 * @package Roose
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class Roose_Config
{
    private static $config = array();
    
    /**
     * 設定ファイルを読み込みます。
     * 
     * @param string $path 設定ファイルのパス
     * @param string|null $namespace 設定を読み込む空間名。
     *  省略された時、ファイル名を名前空間として指定します。
     */ 
    public static function load($path, $namespace = null)
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
        
        
        $conf = @include $path;
        
        if ($conf !== false) {
            Roose_Arr::set(self::$config, $namespace, $conf);
        } else {
            throw new Exception('設定ファイルが読み込めません。(' . $path .')');
        }
    }
    
    /**
     * 指定された設定を取得します。
     * 
     * @param string|array $key 取得したい設定名
     * @param
     */ 
    public static function get($key, $default = null)
    {
        return Roose_Arr::get(self::$config, $key, $default);
    }
}