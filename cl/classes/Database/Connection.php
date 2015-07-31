<?php
namespace CodeLapse\Database;

use \CodeLapse\Database\Connection\MySQL as MySQLConnection;
use \CodeLapse\Database\Connection\PDO as PDOConnection;

/**
 * データベースコネクション抽象化クラス
 *
 * @package CodeLapse\Database
 */
abstract class Connection
{
    const TYPE_PDO = 1;
    const TYPE_MYSQL = 2;


    /**
     * コネクションを生成します。
     *
     * 適切なドライバを自動で選択し、そのドライバのインスタンスを返します。
     *
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password (optional) パスワード
     * @param int|null $driver (optional) ドライバの種類。
     *      指定することで利用するドライバを選択できます。
     * @throws \CodeLapse\Database\DBException
     */
    public static function connect($host, $user, $password = null, $driver = null) {
        $instance = null;

        if ($driver === null) {
            $driver = class_exists('PDO') ? self::TYPE_PDO : self::TYPE_MYSQL;
        }

        switch ($driver) {
            case self::TYPE_PDO     :
                $instance = new PDOConnection($host, $user, $password);
                break;

            case self::TYPE_MYSQL   :
                $instance = new MySQLConnection($host, $user, $password);
                break;
        }

        return $instance;
    }


    /**
     * データベースコネクションを生成します。
     *
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password (optional) パスワード
     */
    public abstract function __construct($host, $user, $password = null);


    /**
     * オブジェクトが内包している、コネクションオブジェクトを取得します。
     *
     * 返り値の型はドライバにより変わるため、一定ではありません。
     *
     * @return resource|PDO
     */
    public abstract function getRawConnection();


    /**
     * 直近に実行したクエリのエラーメッセージを取得します。
     *
     * エラーが発生していない時は空文字を返します。
     *
     * @return string
     */
    public abstract function errorMessage();


    /**
     * 直近に実行したクエリで発生したエラーのコードを取得します。
     *
     * エラーが発生しなかった場合は、0を返します。
     *
     * @return int
     */
    public abstract function errorCode();


    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @return boolean
     */
    public abstract function useDB($dbname);


    /**
     * コネクションで使用する文字コードを設定します。
     *
     * @param string $charset 文字コード
     * @return boolean
     */
    public abstract function setCharset($charset);


    /**
     * クエリーを実行します。
     *
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return CodeLapse\Database\Resultset|boolean
     */
    public abstract function query($sql, $params = null);


    /**
     * トランザクションを開始します。
     *
     * @return boolean
     */
    public abstract function startTransaction();


    /**
     * トランザクションを終了し、実行結果をコミットします。
     *
     * @return boolean
     */
    public abstract function commit();


    /**
     * トランザクションを中止し、トランザクション中に行った処理をすべて無効化します。
     *
     * トランザクション中でない時、DB_Exceptionをスローします。
     *
     * @return boolean
     * @throws \CodeLapse\Database\DBException
     */
    public abstract function rollback();


    /**
     * コネクションがトランザクション中か調べます。
     *
     * @return boolean
     */
    public abstract function inTransaction();


    /**
     * 最後に挿入された行のID、もしくはシーケンス値を返します。
     *
     * @param string $name (optional) シーケンスオブジェクト名
     */
    public abstract function lastInsertId($name = null);


    /**
     * 入力文字列内のシングルクオートをエスケープし、前後に引用符をつけた文字列を返します。
     *
     * @param string $string 文字列
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public abstract function quote($string);


    /**
     * 入力文字列のバッククオートをエスケープし、バッククオートで囲ったをつけた文字列を返します。
     *
     * @param string $string 文字列
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public abstract function quoteIdentifier($string);
}
