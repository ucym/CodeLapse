<?php
/**
 * MySQL接続ユーティリティ
 * 
 * このクラスは"DB"クラスとして利用可能です。
 * 
 * @todo コメント書く
 * @todo 実装チェック
 * @todo MySQL関数依存からの脱却
 * 
 * @package Roose
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class Roose_DB
{
    private static $_config = null;
    private static $_connections = array();
    
    private $_con = null;
    private $_con_name = null;
    
    /**
     * 指定した接続のコネクションインスタンスを取得します。
     * dbconfig.phpの設定を参照します。
     *
     * @return DBConnection DBConnectionインスタンス
     * @throw OutOfBoundsException
     *      dbconfig.phpに記述されていないコネクション名が指定された時にスローされます。
     */
    public static function instance($connection_name = null)
    {
        if (self::$_config === null) {
            self::$_config = Roose_Config::get('db');
        }
        
        $connection_name === null
            and $connection_name = 'default';
        
        // Check is defined in dbconfig.php
        if (array_key_exists($connection_name, self::$_config) === false)
        {
            throw new OutOfBoundsException('Undefined Database host : ' . $connection_name);
        }
        
        // Check already constructed
        if (isset(self::$_connections[$connection_name])) {
            return self::$_connections[$connection_name];
        }
        
        // Create new instance from config
        $conf = self::$_config[$connection_name];
        $host = $conf['host'];
        $user = $conf['user'];
        $pass = $conf['password'];
        $dbname = $conf['database'];

        $instance = new Roose_DB_Connection($host, $user, $pass);
        $instance->_con_name = $connection_name;
        $instance->use_db($dbname);

        self::$_connections[$connection_name] = $instance;
        
        return $instance;
    }
    
    /**
     * 指定されたコネクション名の切断の通知を受けます。
     * 
     * @ignore
     * @param string $con_name コネクション名
     */
    public static function _disconnected($con_name)
    {
        if (array_key_exists($con_name, self::$_connections)) {
            unset(self::$_connections[$con_name]);
        }
    }
    
    /**
     * 指定したコネクションで使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @param string|null $connection コネクション名。指定されない場合、defaultコネクションを利用します。
     * @return boolean
     */
    public static function use_db($db_name, $connection = null)
    {
        return self::instance($connection)->use_db($db_name);
    }
    
    /**
     * 指定したコネクション上でクエリーを実行します。
     *
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @param string|null $connection 接続名。指定されない場合、defaultコネクションを利用します。
     */
    public static function query($sql, $params = null, $connection = null)
    {
        return self::instance($connection)->query($sql, $params);
    }
    
    /**
     * 指定したコネクション上で、最近発生したエラーの内容を取得します。
     * 
     * @param string|null $connection 接続名。指定されない場合、defaultコネクションを利用します。
     * @return string
     */
    public static function error($connection = null)
    {
        return self::instance($connection)->error();
    }
}