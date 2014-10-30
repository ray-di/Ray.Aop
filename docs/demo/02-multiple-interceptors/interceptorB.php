<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class interceptorB implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "before B\n";
        $invocation->proceed();
        echo "after B\n";
    }
}
