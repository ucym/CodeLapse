<?php
/**
 * MySQL関数用 データベース 結果オブジェクト
 *
 * @package CodeLapse\Database\ResultSet
 */
class CL_Database_ResultSet_MySQL extends CL_Database_ResultSet
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
