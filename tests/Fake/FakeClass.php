<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Annotation\FakeClassMarker;
use Ray\Aop\Annotation\FakeMarker;

/**
 * @FakeClassMarker
 */
class FakeClass
{
    public $a = 0;
    public $msg = 'hello';

    public function __toString()
    {
        return 'toStringString';
    }

    /**
     * @FakeMarker
     */
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
     * @Log
     */
    public function getTriple(int $c): int
    {
        return $c * 3;
    }
}
