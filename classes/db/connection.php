<?php
/**
 * データベースコネクションラッパークラス
 * 
 * @todo コメント書く
 * @todo 実装チェック
 * @todo MySQL関数依存からの脱却
 * 
 * @package Roose\DB
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class Roose_DB_Connection
{
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
     */
    public function __construct($host, $user, $password = null, $newConnection = false)
    {
        $this->_con =
            @mysql_connect($host, $user, $password, $newConnection)
                or die('Connection failed. (' . mysql_error() . ')');
    }
    
    
    /**
     * @ignore
     */ 
    public function __destruct()
    {
        $this->disconnect();
    }
    
    
    /**
     * このコネクションを切断します。
     */
    public function disconnect()
    {
        $this->_con !== null
            and mysql_close($this->_con)
            and DB::_disconnected($this);
        
        $this->_con = null;
    }
    
    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     */
    public function useDb($dbname)
    {
        return mysql_select_db($dbname, $this->_con);
//            or die('Select db failed. (' . mysql_error() . ')');
    }
    
    /**
     * クエリーを実行します。
     *
     * @todo 動作確認
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return Roose_DB_Resultset|bool
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
        
        if (is_bool($result)) {
            return $result;
        } else {
            return new Roose_DB_Resultset($result);
        }
    }
    
    
    /**
     * コネクションで使用する文字コードを設定します。
     * @param string $charset 文字コード
     * @return boolean
     */
    public function setCharset($charset)
    {
        return mysql_set_charset($charset, $this->_con);
    }
    
    
    /**
     * トランザクションを開始します。
     * @return boolean
     */
    public function startTransaction($connection = null)
    {
        $result = $this->query('START TRANSACTION');
        
        // トランザクションの開始に成功したら状態を変更する
        $result and $this->_inTransaction = true;
        
        return $result;
    }
    
    
    /**
     * トランザクションを終了し、実行結果をコミットします。
     * @return boolean
     */
    public function commit($connection = null)
    {
        $result = false;
        
        if ($this->_inTransaction) {
            $result = $this->query('COMMIT');
            
            // コミットが完了したら状態を変更する
            $result and $this->_inTransaction = false;
        }
        
        return $result;
    }
    
    
    /**
     * トランザクションを中止し、行った処理をすべて無効化します。
     * @return boolean
     */
    public function rollback($connection = null)
    {
        $result = false;
        
        if ($this->_inTransaction) {
            $result = $this->query('ROLLBACK');
            
            // ロールバックが成功したら状態を切り替える
            $result and $this->_inTransaction = false;
        }
        
        return $result;
    }
    
    
    /**
     * 指定したコネクションがトランザクション中か調べます。
     * @return boolean
     */
    public function inTransaction($connection = null)
    {
        return $this->_inTransaction;
    }
    
    
    /**
     * 最近発生したエラーの内容を取得します。
     * @param string|null $connection 接続名。指定されない場合、defaultコネクションを利用します。
     * @return string
     */
    public function error()
    {
        return mysql_error($this->_con);
    }
}
