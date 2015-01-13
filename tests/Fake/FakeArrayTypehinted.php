<?php

namespace Ray\Aop;

class FakeArrayTypehinted implements FakeArrayInterface
{
    public function invoke(array $array, callable $callable)
    {
    }
}

