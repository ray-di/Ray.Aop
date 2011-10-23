<?php

namespace Ray\Aop;

/**
 * Test class for Ray.Aop
 */
class MockMethod
{
    public $a = 0;

    public function add($n)
    {
        $this->a += $n;
    }

    public function getDouble($a)
    {
        return $a * 2;
    }
}