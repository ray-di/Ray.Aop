<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class EmptyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->proceed();
    }
}
