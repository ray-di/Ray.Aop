<?php
namespace Ray\Aop;

interface FakeArrayInterface
{
    public function invoke(array $array, callable $callable);
}
