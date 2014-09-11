<?php
/**
 * PDO データベースコネクションラッパークラス
 *
 * @package Roose\DB
 */
class Roose_DB_Connection_PDO extends Roose_DB_Connection
{
    /**
     * データベースコネクションを生成します。
     *
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password (optional) パスワード
     */
    public function __construct($host, $user, $password = null)
    {
        try {
            $this->_con = new PDO('mysql:host=' . $host, $user, $password);
        }
        catch (PDOException $e) {
            throw new Roose_DBException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * オブジェクトが内包している、コネクションオブジェクトを取得します。
     *
     * 返り値の型はドライバにより変わるため、一定ではありません。
     * @return PDO
     */
    public function getRawConnection()
    {
        return $this->_con;
    }


    /**
     * 直近に実行したクエリのエラーメッセージを取得します。
     *
     * エラーが発生していない時は空文字を返します。
     * @return string
     */
    public function errorMessage()
    {
        $err = $this->_con->errorInfo();
        return $err[2];
    }


    /**
     * 直近に実行したクエリで発生したエラーのコードを取得します。
     *
     * エラーが発生しなかった場合は、0を返します。
     * @return int
     */
    public function errorCode()
    {
        $err = $this->_con->errorInfo();
        return $err[1] !== null ? $err[1] : 0;
    }


    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @return bool
     */
    public function useDB($dbname)
    {
        return !! $this->query('USE ' . $dbname);
    }


    /**
     * コネクションで使用する文字コードを設定します。
     * @param string $charset 文字コード
     * @return boolean
     */
    public abstract function setCharset($charset)
    {
        return !! $this->query('SET NAMES ?', (array) $charset);
    }


    /**
     * クエリーを実行します。
     *
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return Roose_DB_Resultset|bool
     */
    public function query($sql, $params = null)
    {
        $result = false;
        $stmt = $this->_con->prepare($sql);

        $result = is_array($params) ? $stmt->execute($params) : $stmt->execute();

        if ($result === false) {
            return false;
        }
        else {
            return new Roose_DB_Resultset_PDO($stmt);
        }
    }


    /**
     * トランザクションを開始します。
     * @return boolean
     */
    public function startTransaction()
    {
        return $this->_con->beginTransaction();
    }


    /**
     * トランザクションを終了し、実行結果をコミットします。
     * @return boolean
     */
    public function commit()
    {
        return $this->_con->commit();
    }


    /**
     * トランザクションを中止し、トランザクション中に行った処理をすべて無効化します。
     *
     * トランザクション中でない時、Roose_DBExceptionをスローします。
     *
     * @return boolean
     * @throw Roose_DBException
     */
    public function rollback()
    {
        try {
            return $this->_con->rollback();
        }
        catch (PDOException $e) {
            throw new Roose_DBException('ロールバックに失敗しました。(' . $e->getMessage() . ')', $e->getCode(), $e);
        }
    }


    /**
     * コネクションがトランザクション中か調べます。
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->_con->inTransaction();
    }


    /**
     * 最後に挿入された行のID、もしくはシーケンス値を返します。
     *
     * @param string $name (optional) シーケンスオブジェクト名
     */
    public function lastInsertId($name = null)
    {
        return $this->_con->lastInsertId($name);
    }


    /**
     * 入力文字列内のシングルクオートをエスケープし、前後に引用符をつけたものを返します。
     *
     * @param string $string 文字列
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public function quote($string)
    {
        return $this->_con->quote($string);
    }


    /**
     * 入力文字列のバッククオートをエスケープし、バッククオートで囲ったをつけた文字列を返します。
     *
     * @param string $string 文字列
     * @return string SQLの値として適切な形式に整形された文字列
     */
    public function quoteIdentifier($string)
    {
        $string = mb_ereg_replace('/`/', '``', $string);
        return sprintf('`%s`', $string);
    }
}