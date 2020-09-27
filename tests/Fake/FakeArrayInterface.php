<?php

declare(strict_types=1);

namespace Ray\Aop;

interface FakeArrayInterface
{
    public function invoke(array $array, callable $callable);
}
