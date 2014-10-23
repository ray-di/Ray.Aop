<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocationInterface;

class interceptorB implements MethodInterceptor
{
    public function invoke(MethodInvocationInterface $invocation)
    {
        echo "before B\n";
        $invocation->proceed();
        echo "after B\n";
    }
}
