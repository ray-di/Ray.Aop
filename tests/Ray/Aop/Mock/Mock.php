<?php

namespace Ray\Aop\Mock;

/**
 * Test class for Ray.Aop
 */
class Mock
{
    private $a = 1;
    protected $b = 2;
    public $c = 3;

    public function returnSame($a)
    {
        return $a;
    }

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
