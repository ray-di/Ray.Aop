<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Annotation\FakeClassMarker;
use Ray\Aop\Annotation\FakeMarker;

#[FakeClassMarker]
class FakeClass implements \Stringable
{
    public $a = 0;
    public $msg = 'hello';

    public function __toString(): string
    {
        return 'toStringString';
    }

    #[FakeMarker(1)]
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

    public function getTriple(int $c): int
    {
        return $c * 3;
    }

    public function defaultValue(int $a = 1, $b = null)
    {
    }
}
