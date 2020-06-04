<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class interceptorB implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo 'before B' . PHP_EOL;
        $invocation->proceed();
        echo 'after B' . PHP_EOL;
    }
}
