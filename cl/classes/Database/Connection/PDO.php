<?php
namespace CodeLapse\Database\Connection;

use InvalidArgumentException;
use PDO;
use PDOException;
use CodeLapse\Database\Connection;
use CodeLapse\Database\DBException;
use CodeLapse\Database\ResultSet\PDO as PDOResultSet;

/**
 * PDO データベースコネクションラッパークラス
 *
 * @package CodeLapse\Database
 */
class PDO extends Connection
{
    private $lastStatement = null;

    /**
     * データベースコネクションを生成します。
     *
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password (optional) パスワード
     * @throws CodeLapse\Database\DBException
     */
    public function __construct($host, $user, $password = null)
    {
        try {
            $this->_con = new PDO('mysql:host=' . $host, $user, $password);
        }
        catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
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
        if ($this->lastStatement !== null)
        {
            $err = $this->lastStatement->errorInfo();
        }

        if (! isset($err) or $err[2] === null) {
            $err = $err = $this->_con->errorInfo();
        }

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
        if ($this->lastStatement !== null)
        {
            $err = $this->lastStatement->errorInfo();
        }

        if (! isset($err) or $err[2] === null) {
            $err = $this->_con->errorInfo();
        }

        return $err[1] !== null ? $err[1] : 0;
    }


    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @throws CodeLapse\Database\DBException
     */
    public function useDB($dbname)
    {
        $result = $this->query('USE ' . $dbname);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
    }


    /**
     * コネクションで使用する文字コードを設定します。
     * @param string $charset 文字コード
     * @throws CodeLapse\Database\DBException
     */
    public function setCharset($charset)
    {
        $result = $this->query('SET NAMES ?', (array) $charset);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
    }


    /**
     * クエリーを実行します。
     *
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return CodeLapse\Database\Resultset|bool
     * @throws CodeLapse\Database\DBException
     */
    public function query($sql, $params = null)
    {
        $result = false;
        $stmt = $this->_con->prepare($sql);
        $this->lastStatement = $stmt;

        // PDOのexcuteメソッドがクエリ中にないプレースホルダを渡すことを許容していないため
        // クエリ中に存在しないプレースホルダを事前に削除する
        if (is_array($params)) {
            foreach ($params as $k => & $v) {

                is_int($k) and $k += 1;

                switch (true) {
                    case is_string($v) :
                    case is_float($v) :
                        $stmt->bindParam($k, $v, PDO::PARAM_STR);
                        break;

                    case is_bool($v) :
                        $stmt->bindParam($k, $v, PDO::PARAM_BOOL);
                        break;

                    case is_int($v) :
                        $stmt->bindParam($k, $v, PDO::PARAM_INT);
                        break;

                    case is_null($v) :
                        $stmt->bindParam($k, $v, PDO::PARAM_NULL);
                        break;

                    default :
                        $stmt->bindParam($k, $v, PDO::PARAM_STR);
                }
            }
        }

        $result = $stmt->execute();

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }

        return new PDOResultSet($stmt);
    }


    /**
     * トランザクションを開始します。
     * @throws CodeLapse\Database\DBException
     */
    public function startTransaction()
    {
        $result = $this->_con->beginTransaction();

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
    }


    /**
     * トランザクションを終了し、実行結果をコミットします。
     * @throws CodeLapse\Database\DBException
     */
    public function commit()
    {
        $result = $this->_con->commit();

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
    }


    /**
     * トランザクションを中止し、トランザクション中に行った処理をすべて無効化します。
     *
     * トランザクション中でない時、DBExceptionをスローします。
     *
     * @throws CodeLapse\Database\DBException
     */
    public function rollback()
    {
        try {
            $result = $this->_con->rollback();

            if ($result === false) {
                throw new DBException($this->errorMessage(), $this->errorCode());
            }
        }
        catch (PDOException $e) {
            throw new DBException('ロールバックに失敗しました。(' . $e->getMessage() . ')', $e->getCode(), $e);
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
     * @return int
     * @throws CodeLapse\Database\DBException
     */
    public function lastInsertId($name = null)
    {
        $retsult = $this->_con->lastInsertId($name);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }

        return $result;
    }


    /**
     * 入力文字列内のシングルクオートをエスケープし、前後に引用符をつけたものを返します。
     *
     * @param string $string 文字列
     * @return string SQLの値として適切な形式に整形された文字列
     * @throws CodeLapse\Database\DBException
     */
    public function quote($string)
    {
        $result = $this->_con->quote($string);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }

        return $result;
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
