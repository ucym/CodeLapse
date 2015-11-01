<?php
/**
 * PDO用 データベース 結果オブジェクト
 *
 * @package CodeLapse\Database\ResultSet
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class CL_Database_ResultSet_PDO extends CL_Database_ResultSet
{
    protected function & _fetch(& $resultset)
    {
        $result = $resultset->fetch();
        return $result;
    }

    /**
     * ResultSetオブジェクトを解放します。
     */
    public function free()
    {
        $this->_result !== null
            and $this->_result->closeCursor();

        $this->_result = null;

        parent::free();
    }
}
