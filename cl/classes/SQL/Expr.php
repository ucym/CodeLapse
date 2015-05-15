<?php
namespace CodeLapse\SQL;

class Expr
{
    private $expr;


    /**
     * @param string    $expr   SQL式
     */
    public function __construct($expr)
    {
        $this->expr = $expr;
    }


    public function compile()
    {
        return $this->expr;
    }
}
