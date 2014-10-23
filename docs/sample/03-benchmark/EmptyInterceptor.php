<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocationInterface;

class EmptyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocationInterface $invocation)
    {
        $invocation->proceed();
    }
}
