<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class interceptorA implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "before A\n";
        $invocation->proceed();
        echo "after A\n";
    }
}
