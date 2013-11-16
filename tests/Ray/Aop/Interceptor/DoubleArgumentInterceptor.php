<?php
namespace Ray\Aop\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class DoubleArgumentInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();

        $args[0] *= 2;

        $result = $invocation->proceed();

        return $result;
    }
}
