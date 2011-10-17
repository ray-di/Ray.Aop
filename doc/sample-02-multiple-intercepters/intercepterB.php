<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class intercepterB implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "before B\n";
        $result = $invocation->proceed();
        echo "after B\n";
    }
}