<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use const PHP_EOL;

class interceptorA implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo 'before A' . PHP_EOL;
        $invocation->proceed();
        echo 'after A' . PHP_EOL;
    }
}
