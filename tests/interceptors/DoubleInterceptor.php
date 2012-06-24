<?php
namespace Ray\Aop;

class DoubleInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();

        return $result * 2;
    }
}
