<?php

namespace Ray\Aop\Mock;

/** doc comment of Mock */
class Mock
{
    private $a = 1;
    protected $b = 2;
    public $c = 3;

    /** doc comment of returnSame */
    public function returnSame($a)
    {
        return $a;
    }

    /** doc comment of getSub */
    public function getSub($a, $b)
    {
        return $a - $b;
    }

    public function returnValue(Num $num = null)
    {
        return $num->value;
    }

    public function getPrivateVal()
    {
        return $this->a;
    }
}
