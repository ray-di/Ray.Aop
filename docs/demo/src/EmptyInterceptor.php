<?php
namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class EmptyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->proceed();
    }
}
