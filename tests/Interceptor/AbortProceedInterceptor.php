<?php
namespace Ray\Aop\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class AbortProceedInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return 20;
    }
}
