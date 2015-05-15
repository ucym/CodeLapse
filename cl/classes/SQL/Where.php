<?php
namespace CodeLapse\SQL;

/**
 * @link https://github.com/fuel/core/blob/1.8/develop/classes/database/query/builder/where.php パクリ元
 */
class Where
{
    protected $conditions = array();


    protected $limit = null;


    /**
     * ANDで条件式を追加します。
     *
     * @param string|callable   $field      フィールド名
     * @param string            $op         演算子('=', '<', '<>' など)
     * @param mixed             $value      比較する値（プレースホルダが埋め込み可能です）
     * @return D5_Sql_Where
     * @throws D5_DBException
     */
    public function andWhere($field, $op = null, $value = null)
    {
        if (is_callable($field)) {
            $this->andOpen();
            $field($this);
            $this->andClose();

            return $this;
        }

        if (func_num_args() < 2) {
            throw new D5_DBException('where条件の値が必要です。');
        }

        if ($value === null) {
            $value = $op;
            $op = '=';
        }

        $this->conditions[] = array('AND' => array($field, '=', $value));
        return $this;
    }


    /**
     * ORで条件式を追加します。
     *
     * @param string|callable   $field      フィールド名
     * @param string            $op         演算子('=', '<', '<>' など)
     * @param mixed             $value      比較する値（プレースホルダが埋め込み可能です）
     * @return D5_Sql_Where
     * @throws D5_DBException
     */
    public function orWhere($field, $op = null, $value = null)
    {
        if (is_callable($field)) {
            $this->orOpen();
            $field($this);
            $this->orClose();

            return $this;
        }

        if (func_num_args() < 2) {
            throw new D5_DBException('where条件の値が必要です。');
        }

        if ($value === null) {
            $value = $op;
            $op = '=';
        }

        $this->conditions[] = array('OR' => array($field, $op, $value));
        return $this;
    }


    /**
     * andWhereの別名
     *
     * @param string    $field      フィールド名
     * @param string    $op         演算子('=', '<', '<>' など)
     * @param mixed     $value      比較する値（プレースホルダが埋め込み可能です）
     * @return D5_Sql_Where
     * @throws D5_DBException
     */
    public function where($field, $op = null, $value = null)
    {
        return call_user_func_array(array($this, 'andWhere'), func_get_args());
    }


    /**
     * 他の条件とANDで結合される開き括弧を生成します。
     *
     * @return \D5_Sql_Where
     */
    public function andOpen()
    {
        $this->conditions[] = array('AND' => '(');
        return $this;
    }

    /**
     * 閉じ括弧を挿入します。
     *
     * @return \D5_Sql_Where
     */
    public function andClose()
    {
        $this->conditions[] = array('AND' => ')');
        return $this;
    }


    public function orOpen()
    {
        $this->conditions[] = array('OR' => '(');
        return $this;
    }


    public function orClose()
    {
        $this->conditions[] = array('OR' => ')');
        return $this;
    }


    /**
     * 取得件数の上限を設定します。
     *
     * @param int $limit 取得件数の上限
     * @return \D5_Sql_Where
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }


    /**
     * SQLを生成します。
     *
     * @return string
     */
    public function compile(D5_DB_Connection $db)
    {
        $buffer = array();

        $prevCond = null;
        foreach ($this->conditions as & $cond) {
            if ($prevCond !== null) {
                $buffer[] = $prevCond[3];
            }

            // $cond = array('AND | OR' => array(field, condition, value))
            $value = $cond[2] instanceof D5_Sql_Expr ? $cond[2]->compile() : $db->quote($cond[2]);
            $buffer[] = sprintf('%s %s %s', $db->quoteIdentifier($cond[0]), $cond[1], $value);

            $prevCond = & $cond;
        }

        return implode(' ', $buffer);
    }
}
