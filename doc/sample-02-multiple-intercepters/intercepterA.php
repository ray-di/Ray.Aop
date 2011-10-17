<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class intercepterA implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "before A\n";
        $result = $invocation->proceed();
        echo "after A\n";
    }
}