<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocationInterface;

class interceptorA implements MethodInterceptor
{
    public function invoke(MethodInvocationInterface $invocation)
    {
        echo "before A\n";
        $invocation->proceed();
        echo "after A\n";
    }
}
