<?php
/**
 * MySQL関数用 データベース 結果オブジェクト
 *
 * @package Roose\DB
 */
class Roose_DB_Resultset_Mysql implements Roose_DB_Resultset
{
    protected function & _fetch(& $resultset)
    {
        $result = mysql_fetch_array($resultset);
        return $result;
    }

    /**
     * ResultSetオブジェクトを解放します。
     */
    public function free()
    {
        $this->_result !== null
            and mysql_free_result($this->_result);

        $this->_result = null;

        parent::free();
    }
}
