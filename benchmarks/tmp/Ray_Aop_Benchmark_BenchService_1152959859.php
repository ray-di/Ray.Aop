<?php

declare(strict_types=1);

namespace Ray\Aop\Benchmark;

class BenchService_1152959859 extends BenchService implements \Ray\Aop\WeavedInterface 
{
    use \Ray\Aop\InterceptTrait;
    public function doWork(int $a, int $b): int
    {
    $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'doWork', func_get_args(), $this->bindings['doWork'], parent::doWork(...));
    return $__aop->proceed();    }

    public function doString(string $s): string
    {
    $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'doString', func_get_args(), $this->bindings['doString'], parent::doString(...));
    return $__aop->proceed();    }
}