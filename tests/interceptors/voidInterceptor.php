<?php
namespace Ray\Aop;

class voidInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
    }
}
