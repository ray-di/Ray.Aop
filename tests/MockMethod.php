<?php

namespace Ray\Aop;

/**
 * Test class for Ray.Aop
 */
class MockMethod
{
    public $a = 0;

    public $msg = "hello";

    public function add($n)
    {
        $this->a += $n;
    }

    public function getDouble($a)
    {
        return $a * 2;
    }

    public function getSub($a, $b)
    {
        return $a - $b;
    }

    /**
     * @param  unknown_type $c
     * @return number
     *
     * @Log
     */
    public function getTriple($c)
    {
        return $c * 3;
    }

    public function __toString()
    {
        return 'toStringString';
    }
}
