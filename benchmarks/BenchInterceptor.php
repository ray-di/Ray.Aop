<?php

declare(strict_types=1);

namespace Ray\Aop\Benchmark;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

final class BenchInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation): mixed
    {
        return $invocation->proceed();
    }
}
