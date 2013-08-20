<?php

namespace Ray\Aop\Mock;

/**
 * Test class for Ray.Aop
 */
class Mock
{
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
}
