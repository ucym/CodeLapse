<?php
/**
 * データベースの結果オブジェクト
 * 
 * @todo コメント書く
 * @todo 実装
 * @todo MySQL関数依存からの脱却
 * 
 * @package Roose\DB
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class Roose_DB_Resultset implements Iterator
{
    const FETCH_BOTH = 1;
    const FETCH_NUM = 2;
    const FETCH_ASSOC = 3;
    
    
    /**
     * @var resource|mixed データベースから取得したリザルトセット
     */ 
    private $_result = null;
    
    /**
     * @var int イテレータ用ポインタ
     */ 
    private $_itr_index = -1;
    
    /**
     * @var int fetchメソッド用カウンタ
     */
    private $_cursor = -1;
    
    /**
     * @var array データベースから取得した生の結果データ
     */
    private $_plaindata = array();
    
    
    /**
     * @param resource|mixed $result リザルトセット
     */
    public function __construct($result)
    {
//        if (is_resource($result) === false) {
//            throw new InvalidArgumentException('渡された引数は');
//        } else {
            $this->_result = $result;
            $this->next();
//        }
    }
    
    
    /**
     * Iterator実装メソッド
     * @ignored
     */
    public function current()
    {
        if (isset($this->_plaindata[$this->_itr_index])) {
            return $this->_plaindata[$this->_itr_index];
        }
        
        return null;
    }
    
    
    /**
     * Iterator実装メソッド
     * @ignored
     */
    public function key()
    {
        return $this->_itr_index;
    }
    
    
    /**
     * Iterator実装メソッド
     * @ignored
     */
    public function next()
    {
        $i = $this->_itr_index + 1;
        
        if (isset($this->_plaindata[$i]) === false) {
            // next data isn't fetched? fetch.
            $this->_plaindata[] = mysql_fetch_array($this->_result, MYSQL_ASSOC);
        }
        
        $this->_itr_index++;
    }
    
    
    /**
     * Iterator実装メソッド
     * @ignored
     */
    public function rewind()
    {
        $this->_itr_index = 0;
    }
    
    
    /**
     * Iterator実装メソッド
     * @ignored
     */
    public function valid()
    {
        return
            isset($this->_plaindata[$this->_itr_index])
                and $this->_plaindata[$this->_itr_index] !== false;
    }
    
    
    /**
     * 生のResultSetオブジェクトを取得します。
     * @return mixed
     */
    public function getResultSet()
    {
        return $this->_result;
    }
    
    
    /**
     * 現在のカーソル位置の次のレコードを取得します。
     */
    public function fetch()
    {
        $ret = false;
        $i = $this->_cursor + 1;
        
        if (isset($this->_plaindata[$i]))  {
            $ret = $this->_plaindata[$i];
        } else {
            $ret = mysql_fetch_array($this->_result, MYSQL_ASSOC);
            $this->_plaindata[$i] = $ret;
        }
        
        $this->_cursor++;
        
        return $ret;
    }
    
    
    /**
     * 結果を全件取得します。
     * @return array
     */
    public function fetchAll($type = self::FETCH_ASSOC)
    {
        $result = array();
        
        switch ($type) {
            case self::FETCH_BOTH :
                foreach ($this as $k => $row) {
                    $result[$k] = $row;
                    
                    // ASSOCで取得した配列に、番号列を追加する
                    $i = 0;
                    foreach ($row as $value) {
                        $result[$k][$i++] = $value;
                    }
                }
                
                break;
                
            case self::FETCH_NUM :
                // ASSOCで取得した配列の添え字を番号列に変更
                foreach ($this as $k => $row) {
                    $result[$k] = array();

                    $i = 0;
                    foreach ($row as $value) {
                        $result[$k][$i++] = $value;
                    }
                }
                break;

            case self::FETCH_ASSOC :
                foreach ($this as $k => $v) {
                    var_dump($v);
                    $result[$k] = $v;
                }
                break;
        }
        
        return $result;
    }
    
    
    /**
     * ResultSetオブジェクトを解放します。
     */
    public function free()
    {
        $this->result !== null
            and mysql_free_result($this->_result);
        
        unset($this->_result);
        unset($this->_plaindata);
    }
}
