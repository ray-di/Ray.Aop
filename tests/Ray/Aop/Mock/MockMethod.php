<?php

namespace Ray\Aop\Mock;

/**
 * Test class for Ray.Aop
 */
class MockMethod
{
    public $msg = "hello";

    public function add($n)
    {
        $this->a += $n;
    }

    public function returnSame($a)
    {
        return $a;
    }

    public function getSub($a, $b)
    {
        return $a - $b;
    }

    /**
     * @param  int $c
     *
     * @return number
     *
     * @Log
     */
    public function getTriple($c)
    {
        return $c * 3;
    }

    public function duplicatedParamName($a, $a)
    {
    }

    public function __toString()
    {
        return 'toStringString';
    }
}
