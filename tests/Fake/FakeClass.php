<?php
namespace Ray\Aop;

class FakeClass
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
     * @param int $c
     *
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
