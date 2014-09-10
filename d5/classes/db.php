<?php
/**
 * MySQL接続ユーティリティ
 * 
 * このクラスはD5ライブラリ環境下（bs.phpを読み込んだ状態）では "DB"クラスとして利用可能です。
 * 単体ライブラリとして利用しているときは D5_DBがクラス名になります。
 * 
 * ```php
 * //-- D5(bs.php)を読み込んだ時
 * require 'pathTo/D5/bs.php';
 * $result = DB::query('SOME SQL QUERY');
 * 
 * //-- db.phpを単体で読み込んだ時
 * require 'pathToDb/db.php';
 * $con = D5_DB::instance('hostName', 'user', 'password');
 * $result = $con->query('SOME SQL QUERY');
 * ```
 * 
 * ### データベース接続からSQL送信までの流れ
 * #### db.php を単体で読み込んだ時
 * ```php
 * // （例: userid => lzm, password => wox#a'zlp）
 * $userid = $_POST['userid'];
 * $password = $_POST['password'];
 * 
 * // DBと接続する
 * $con = D5_DB::instance('localhost', 'user', 'password');
 * 
 * // クエリを送る
 * // 配列に渡した値が、クエリに埋め込まれていることに注目。
 * // （送信されるクエリ: SELECT * FROM Users WHERE userid = 'lzm' AND password = 'wox#a\'zlp'）
 * $result = $con->query(
 *      'SELECT * FROM Users WHERE userid = :id AND password = :pass',
 *      array(':id' => $userid, ':pass' => $password)
 * );
 * 
 * if ($result->fetch() !== false) {
 *      echo 'ログイン成功';
 * } else { 
 *      echo 'ログイン失敗';
 * }
 * 
 * ```
 * 
 * #### bs.phpを取り込んだ時
 * ```php
 * //!! （事前に "config/db.php"を設定しておく必要があります。）
 * 
 * // （例: userid => lzm, password => wox#a'zlp）
 * $userid = Input::post('userid');
 * $password = Input::post('password');
 * 
 * // クエリを送る
 * // 配列に渡した値が、クエリに埋め込まれていることに注目。
 * // （送信されるクエリ: SELECT * FROM Users WHERE userid = 'lzm' AND password = 'wox#a\'zlp'）
 * $result = DB::query(
 *      'SELECT * FROM Users WHERE userid = :id AND password = :pass',
 *      array(':id' => $userid, ':pass' => $password)
 * );
 * 
 * if ($result->fetch() !== false) {
 *      echo 'ログイン成功';
 * } else { 
 *      echo 'ログイン失敗';
 * }
 * 
 * ```
 * 
 * **取得した結果を全件表示する**
 * ```php
 * //-- D5ライブラリ使用中の場合を想定
 * // SQLの実行結果は $result に入っている
 * foreach ($result as $row) {
 *      // $rowに一行分のデータが入ってくる
 *      print_r($row);
 * }
 * 
 * ```
 * 
 * @todo コメント書く
 * @todo 実装チェック
 * @todo MySQL関数依存からの脱却
 * 
 * @package D5
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class D5_DB
{
    /**
     * 読み込んだ設定情報
     * @ignore
     */ 
    private static $_config = null;
    
    /**
     * 接続設定ごとのコネクションインスタンス
     * @ignore
     */
    private static $_connections = array();
    
    
    /**
     * 指定した接続のコネクションインスタンスを取得します。
     * 
     * パラメータを二つ以上指定した時、<br>
     * 最初のパラメーターを接続先ホストとして<br>
     * 続く２つのパラメーターをユーザー名、パスワードとして、データベースに接続します。<br>
     * <br>
     * パラメータが一つしか指定されない場合、<br>
     * 接続設定は config/db.phpの設定を参照します。<br>
     * config/db.php に接続設定を記述するか<br>
     * Configクラスを利用して、db名前空間に接続設定を読み込んでください。<br>
     * <br>
     * ** 単体ライブラリとして利用する場合、ホスト名・ユーザーは必ず指定する必要があります。**
     * 
     * @param string $connection_name 使用する接続名
     * @param null|string $user (optional) ユーザー名
     * @param null|string $password (optional) パスワード
     * @param null|boolean $newConnection (optional) 接続を新規生成するか指定します
     * @return DBConnection DBConnectionインスタンス
     * @throw OutOfBoundsException
     *      dbconfig.phpに記述されていないコネクション名が指定された時にスローされます。
     * @throw InvalidArgumentException
     *      単体ライブラリとして利用しているときに、ホスト名とユーザー名が指定されない時に発生します。
     */
    public static function instance($connection_name = null, $user = null, $password = null, $newConnection = false)
    {
        $newConnection = !!$newConnection;
        
        if (func_num_args() > 2) {
            $host = $connection_name;
            
            // 仮の接続名を生成
            $connection_name = implode(':', array($host, $user, md5($password)));
            
            // 既存のコネクションがあれば、そちらを返す。
            // （接続を新規生成するときを除いて）
            if ($newConnection == false and isset(self::$_connections[$connection_name]))
            {
                return　self::$_connections[$connection_name];
            }
            
            // 接続を新規生成する
            $instance = new D5_DB_Connection($host, $user, $password, true);
            
            // 接続インスタンスを保持
            // ただし、新規生成したコネクションは保持しない。
            //  - 共用しても良いコネクションなら新規生成しないだろうと想定して
            if ($newConnection == false)
            {
                self::$_connections[$connection_name] = $instance;
            }
            
            return $instance;
        }
        
        if (class_exists('D5_Config')) {
            if (self::$_config === null) {
                self::$_config = D5_Config::get('db', array());
            }

            $connection_name === null
                and $connection_name = 'default';

            if (array_key_exists($connection_name, self::$_config) === false)
            {
                // 接続設定に指定されたコネクション用の設定がなければ
                // 例外を投げる
                throw new OutOfBoundsException('Undefined Database host : ' . $connection_name);
            }

            if (isset(self::$_connections[$connection_name])) {
                // 指定されたコネクションがすでに生成されていたら
                // そのコネクションを返す。
                return self::$_connections[$connection_name];
            }

            // 新しいコネクションを生成する
            $conf = D5_Arr::get(self::$_config, $connection_name);

            if ($conf === null) {
                throw new Exception('接続設定が定義されていません。(' . $connection_name . ')');
            }
        
            $host = isset($conf['host']) ? $conf['host'] : null;
            $user = isset($conf['user']) ? $conf['user'] : null;
            $pass = isset($conf['password']) ? $conf['password'] : null;
            $dbname = isset($conf['database']) ? $conf['database'] : null;

            $instance = new D5_DB_Connection($host, $user, $pass);
            is_string($dbname) and $instance->useDb($dbname); // 使用するデータベースを選択する

            self::$_connections[$connection_name] = $instance;
            
            return $instance;
        } else {
            throw new InvalidArgumentException(
                '単体ライブラリとして利用されているので、接続名から接続を生成することはできません。' .
                'ホスト名とユーザー名を指定してください。'
            );
        }
    }
    
    
    /**
     * 指定されたコネクション名の切断の通知を受けます。
     * 
     * @ignore
     * @param D5_DB_Connection $con コネクションオブジェクト
     */
    public static function _disconnected($con)
    {
        foreach (self::$_connections as $k => $v) {
            if ($v === $con) {
                unset(self::$_connections[$k]);
                break;
            }
        }
    }
    
    
    /**
     * 指定したコネクションで使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @param string|null $connection コネクション名。指定されない場合、defaultコネクションを利用します。
     * @return boolean
     */
    public static function useDb($db_name, $connection = null)
    {
        return self::instance($connection)->useDb($db_name);
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
     * コネクションで使用する文字コードを設定します。
     * @param string $charset 文字コード
     * @return boolean
     */
    public static function setCharset($charset, $connection = null)
    {
        return self::instance($connection)->setCharset($charset);
    }
    
    
    /**
     * トランザクションを開始します。
     * @return boolean
     */
    public static function startTransaction($connection = null)
    {
        return self::instance($connection)->startTransaction();
    }
    
    
    /**
     * トランザクションを終了し、実行結果をコミットします。
     * @return boolean
     */
    public static function commit($connection = null)
    {
        return self::instance($connection)->commit();
    }
    
    
    /**
     * トランザクションを中止し、行った処理をすべて無効化します。
     * @return boolean
     */
    public static function rollback($connection = null)
    {
        return self::instance($connection)->rollback();
    }
    
    
    /**
     * 指定したコネクションがトランザクション中か調べます。
     * @return boolean
     */
    public static function inTransaction($connection = null)
    {
        return self::instance($connection)->inTransaction();
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
    
    
    /**
     * @ignore
     */
    private function __construct() {}
}
