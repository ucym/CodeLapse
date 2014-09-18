<?php
/**
 * PDO用 データベース 結果オブジェクト
 *
 * @package Roose\DB
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class D5_DB_Resultset_PDO extends D5_DB_Resultset
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
