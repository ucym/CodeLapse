<?php
namespace CodeLapse\Database\Connection;

use InvalidArgumentException;
use CodeLapse\Database\Connection;
use CodeLapse\Database\DBException;
use CodeLapse\Database\ResultSet\MySQL as MySQLResultSet;

/**
 * MySQL データベースコネクションラッパークラス
 *
 * @package CodeLapse\Database\Connection
 */
class MySQL extends Connection
{

    /**
     * マルチバイトに対応したsubstr_replace関数
     *
     * @see https://gist.github.com/stemar/8287074 mb_substr_replace
     * @param string|array $string 入力文字列
     * @param string $replacement
     */
    private static function mb_substr_replace($string, $replacement, $start, $length = null)
    {
        if (is_array($string)) {
            $num = count($string);
            // $replacement
            $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
            // $start
            if (is_array($start)) {
                $start = array_slice($start, 0, $num);
                foreach ($start as $key => $value)
                    $start[$key] = is_int($value) ? $value : 0;
            }
            else {
                $start = array_pad(array($start), $num, $start);
            }
            // $length
            if (!isset($length)) {
                $length = array_fill(0, $num, 0);
            }
            elseif (is_array($length)) {
                $length = array_slice($length, 0, $num);
                foreach ($length as $key => $value)
                    $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
            }
            else {
                $length = array_pad(array($length), $num, $length);
            }
            // Recursive call
            return array_map(array(__CLASS__, __METHOD__), $string, $replacement, $start, $length);
        }
        preg_match_all('/./us', (string)$string, $smatches);
        preg_match_all('/./us', (string)$replacement, $rmatches);

        if ($length === null) $length = mb_strlen($string);

        array_splice($smatches[0], $start, $length, $rmatches[0]);

        return implode($smatches[0]);
    }


    /**
     * データベースコネクション
     * @var resource
     */
    private $_con = null;


    /**
     * トランザクション中かどうかを示します。
     * @var boolean
     */
    private $_inTransaction = false;


    /**
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password パスワード
     * @param boolean|null $newConnection (optional) 新規コネクションを生成するか
     * @throws CodeLapse\Database\DBException
     */
    public function __construct($host, $user, $password = null)
    {
        $this->_con = @mysql_connect($host, $user, $password, $newConnection);

        if ($this->_con == false) {
            throw new DBException('データベースへの接続に失敗しました。(' . mysql_error() . ')', mysql_errno());
        }
    }


    /**
     * @ignore
     */
    public function __destruct()
    {
        mysql_close($this->_con);
    }


    /**
     * オブジェクトが内包している、コネクションオブジェクトを取得します。
     * @return resource
     */
    public function getRawConnection()
    {
        return $this->_con;
    }


    /**
     * 直近実行したクエリのエラーの内容を取得します。
     *
     * エラーが発生しなかった場合、空文字を返します。
     * @return string
     */
    public function errorMessage()
    {
        return mysql_error($this->_con);
    }


    /**
     * 直近実行したクエリのエラーコードを取得します。
     * @return int
     */
    public function errorCode()
    {
        return mysql_errno($this->_con);
    }


    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     * @throws CodeLapse\Database\DBException
     */
    public function useDB($dbname)
    {
        if (@ mysql_select_db($dbname, $this->_con) === false) {
            throw new DBException('データベースの選択に失敗しました。(' . $this->errorMessage() . ')', $this->errorCode());
        }
    }


    /**
     * クエリーを実行します。
     *
     * @todo 動作確認
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return CodeLapse\Database\Resultset|bool
     * @throws CodeLapse\Database\DBException
     */
    public function query($sql, $params = null)
    {
        if (is_array($params)) {
            //-- パラメータが設定されていれば埋め込む
            foreach ($params as $key => $value) {

                // キーが数値ならば、SQL中の 疑問符プレースホルダを探す
                if (is_int($key)) {
                    $ph_pos = mb_strpos($sql, '?');

                    if ($ph_pos !== false) {

                        if (is_string($value)) {
                            $value = sprintf('\'%s\'', mysql_real_escape_string($value, $this->_con));
                        } else if (is_bool($value)) {
                            $value = $value ? 'TRUE' : 'FALSE';
                        } else if (is_null($value)) {
                            $value = 'NULL';
                        }

                        $sql = substr_replace($sql, $value, $ph_pos, 1);
                    }

                    continue;
                }

                // キーが文字列ならば、SQL文中の :**プレースホルダを探す。
                if (is_string($key)) {
                    $ph_pos = strpos($sql, $key);

                    if ($key[0] !== ':') {
                        throw new InvalidArgumentException("不正なプレースホルダがパラメータに含まれています($key)");
                    }

                    if ($ph_pos !== false) {
                        if (is_string($value)) {
                            $value = sprintf('\'%s\'', mysql_real_escape_string($value, $this->_con));
                        } else if (is_bool($value)) {
                            $value = $value ? 'TRUE' : 'FALSE';
                        } else if (is_null($value)) {
                            $value = 'NULL';
                        }

                        $sql = str_replace($key, $value, $sql);
                    }

                    continue;
                }
            }
        }

        $result = mysql_query($sql, $this->_con);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
        else if ($result === true) {
            return $result;
        }
        else {
            return new MySQLResultSet($result);
        }
    }


    /**
     * コネクションで使用する文字コードを設定します。
     * @param string $charset 文字コード
     * @throws CodeLapse\Database\DBException
     */
    public function setCharset($charset)
    {
        $result = mysql_set_charset($charset, $this->_con);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }
    }


    /**
     * トランザクションを開始します。
     * @throws CodeLapse\Database\DBException
     */
    public function startTransaction()
    {
        $result = $this->query('START TRANSACTION');

        // トランザクションの開始に成功したら状態を変更する
        $result and $this->_inTransaction = true;
    }


    /**
     * トランザクションを終了し、実行結果をコミットします。
     * @throws CodeLapse\Database\DBException
     */
    public function commit()
    {
        if ($this->_inTransaction === false) {
            return false;
        }

        $result = $this->query('COMMIT');

        // コミットが完了したら状態を変更する
        $result and $this->_inTransaction = false;
    }


    /**
     * トランザクションを中止し、行った処理をすべて無効化します。
     * @throws CodeLapse\Database\DBException
     */
    public function rollback()
    {
        if ($this->_inTransaction === false) {
            throw new DBException('トランザクション外でrollbackメソッドが実行されました。');
        }

        $result = $this->query('ROLLBACK');

        // ロールバックが成功したら状態を切り替える
        $result and $this->_inTransaction = false;
    }


    /**
     * 指定したコネクションがトランザクション中か調べます。
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->_inTransaction;
    }


    /**
     * 最後に挿入された行のID、もしくはシーケンス値を返します。
     * @param string $name (optional) シーケンスオブジェクト名
     * @return int
     * @throws CodeLapse\Database\DBException
     */
    public function lastInsertId($name = null)
    {
        $result = mysql_insert_id($this->_con);

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
        $result = mysql_real_escape_string($string, $this->_con);

        if ($result === false) {
            throw new DBException($this->errorMessage(), $this->errorCode());
        }

        return sprintf('\'%s\'', $result);
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
