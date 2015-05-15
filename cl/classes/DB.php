<?php
namespace CodeLapse;

/**
 * データベースの例外クラス
 */
class DBException extends Exception {}

/**
 * データベースユーティリティクラス
 *
 * ```php
 * //-- CodeLapse(bs.php)を読み込んだ時
 * require 'pathToCodeLapse/bootstrap.php';
 * $result = DB::query('SELECT `somefield` FROM `sometable`');
 *
 * //-- db.phpを単体で読み込んだ時
 * require 'pathToDb/db.php';
 * $con = DB::connect('hostName', 'user', 'password');
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
 * $con = DB::connect('localhost', 'user', 'password');
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
 * #### bootstrap.phpを取り込んだ時
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
 * //-- CodeLapseライブラリ使用中の場合を想定
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
 * @package CodeLapse
 */
class DB
{
    const ERR_CONNECTION_FAILED = 100;

    const DEFAULT_CONNECTION_NAME = 'default';

    /**
     * 接続設定ごとのコネクションインスタンス
     *
     * @ignore
     */
    private static $_connections = array();


    /**
     * 指定した接続のコネクションインスタンスを取得します。
     *
     * 既存のコネクション、もしくは接続設定ファイルからコネクションを生成します。<br>
     * <br>
     * 接続設定は config/db.phpの設定を参照します。<br>
     * Configクラスを利用して、"db"名前空間に接続設定を読み込んでください。<br>
     *
     * @param string|null $connectionName (オプション) 取得する接続名
     * @throw OutOfBoundsException
     *      dbconfig.phpに記述されていないコネクション名が指定された時にスローされます。
     * @throw InvalidArgumentException
     *      単体ライブラリとして利用しているときに、ホスト名とユーザー名が指定されない時に発生します。
     */
    public static function instance($connectionName = null)
    {
        ! is_string($connectionName)
            and $connectionName = self::DEFAULT_CONNECTION_NAME;

        if (array_key_exists($connectionName, self::$_connections)) {
            return self::$_connections[$connectionName];
        }

        // Configクラスが読み込まれていなければ
        // これより先の処理は続行できない
        if (! class_exists('Config')) {
            if (count(self::$_connections) === 0) {
                throw new OutOfBoundsException('インスタンスを取得する前に connectメソッドでデータベースへ接続している必要があります。');
            }
            else {
                throw new OutOfBoundsException('定義されていないコネクションが要求されました。(接続名: ' .$connectionName . ')');
            }
        }

        $config = Config::get('db', array());

        if (array_key_exists($connectionName, $config) === false)
        {
            // 接続設定に指定されたコネクション用の設定がなければ、例外を投げる
            throw new OutOfBoundsException('定義されていないコネクションが要求されました。(接続名: ' .$connectionName . ')');
        }

        // 新しいコネクションを生成する
        $conf = $config[$connectionName];

        if ($conf === null) {
            throw new OutOfBoundsException('定義されていないコネクションが要求されました。(接続名: ' .$connectionName . ')');
        }

        $host   = Arr::get($conf, 'host');
        $user   = Arr::get($conf, 'user');
        $pass   = Arr::get($conf, 'password');
        $dbname = Arr::get($conf, 'database');
        $charset = Arr::get($conf, 'charset');

        $con = self::connect($host, $user, $pass, false, $connectionName);
        ! empty($dbname) and $con->useDB($dbname);
        ! empty($charset) and $con->setCharset($charset);

        return $con;
    }


    /**
     * データベースへ接続します。
     *
     * オープンされたコネクションは内部に保持されます。
     * 実行中、最初に開かれたコネクションを標準コネクションとして使用します。
     *
     * ```php
     * try {
     *     $con = DB::connect(DB_HOST, DB_USER, DB_PASSWD);
     * }
     * catch (DB_Exception $e) {
     *     // 接続に失敗
     * }
     * ```
     *
     * @param string       $host           ホスト名
     * @param string       $user           ユーザー名
     * @param null|string  $password       (optional) パスワード
     * @param null|boolean $newConnection  (optional) 既存のコネクションを使用しないか指定します。
     * @param null|string  $connectionName (optional) コネクションに対する名前。
     *      instanceメソッドでインスタンスを取得する際に指定する名前です。
     * @return DB_Connection DBConnectionインスタンス
     * @throw DB_Exception
     *      データベース接続に失敗した時にスローされます。
     */
    public static function connect($host, $user, $passwd = null, $newConnection = false, $connectionName = null)
    {
        try {
            $already_exists = false;

            // 接続名が指定されていなければ自動生成
            is_string($connectionName) === false
                and $connectionName = implode(':', array($host, $user));

            // 同じ名前のコネクションが存在し、
            // 既存のコネクションを利用する場合は、既存のコネクションを返す
            $already_exists = array_key_exists($connectionName, self::$_connections);
            if ($already_exists and $newConnection === false)
            {
                return self::$_connections[$connectionName];
            }

            // 新しい接続を生成
            $instance = DB_Connection::connect($host, $user, $passwd);

            $already_exists === false
                and self::$_connections[$connectionName] = $instance;

            // 実行中で最初に接続したコネクションをデフォルトに設定
            count(self::$_connections) === 0
                and self::$_connections[self::DEFALT_CONNECTION_NAME] = $instance;

            return $instance;
        }
        catch (DB_Exception $e) {
            throw $e;
        }
    }


    /**
     * 指定したコネクション上で、最近実行したクエリで発生したエラーの内容を取得します。
     *
     * エラーが発生していない時は、空文字を返します。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return string
     */
    public static function errorMessage($connection = null)
    {
        return self::instance($connection)->errorMessage();
    }


    /**
     * 指定されたコネクション上で、最近実行したクエリで発生したエラーのコードを取得します。
     *
     * エラーが発生しなかった場合は、0を返します。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return int
     */
    public static function errorCode($connection = null)
    {
        return self::instance($connection)->errorCode();
    }


    /**
     * 指定したコネクションで使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     */
    public static function useDB($db_name, $connection = null)
    {
        return self::instance($connection)->useDB($db_name);
    }


    /**
     * コネクションで使用する文字コードを設定します。
     *
     * @param string $charset 文字コード
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     */
    public static function setCharset($charset, $connection = null)
    {
        return self::instance($connection)->setCharset($charset);
    }


    /**
     * 指定したコネクション上でクエリーを実行し���す���
     *
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params (optional) クエリに埋め込むパラメータ
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     */
    public static function query($sql, $params = null, $connection = null)
    {
        return self::instance($connection)->query($sql, $params);
    }


    /**
     * 指定したコネクションでトランザクションを開始します。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     */
    public static function startTransaction($connection = null)
    {
        return self::instance($connection)->startTransaction();
    }


    /**
     * 指定したコネクションのトランザクションを終了し、実行結果をコミットします。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     */
    public static function commit($connection = null)
    {
        return self::instance($connection)->commit();
    }


    /**
     * 指定したコネクション上のトランザクションを中止し、行った処理をすべて無効化します。
     *
     * トランザクション中でない時、DB_Exceptionをスローします。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     * @throw DB_Exception
     */
    public static function rollback($connection = null)
    {
        return self::instance($connection)->rollback();
    }


    /**
     * 指定したコネクションがトランザクション中か調べます。
     *
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return boolean
     */
    public static function inTransaction($connection = null)
    {
        return self::instance($connection)->inTransaction();
    }


    /**
     * 指定されたコネクション上で最後に挿入された行のID、もしくはシーケンス値を返します。
     *
     * @param string $name (optional) シーケンスオブジェクト名
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     */
    public static function lastInsertId($name = null, $connection = null)
    {
        return self::instance($connection)->lastInsertId($name);
    }


    /**
     * 入力文字列内のシングルクオートをエスケープし、前後に引用符をつけた文字列を返します。
     *
     * @param string $string 文字列
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public static function quote($string, $connection = null)
    {
        return self::instance($connection)->quote($string);
    }


    /**
     * 入力文字列のバッククオートをエスケープし、バッククオートで囲ったをつけた文字列を返します。
     *
     * @param string $string 文字列
     * @param string|null $connection (optional) 接続名。指定されない場合、初期コネクションを利用します。
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public static function quoteIdentifier($string, $connection = null)
    {
        return self::instance($connection)->quoteIdentifier($string);
    }


    /**
     * @ignore
     */
    private function __construct() {}
}
