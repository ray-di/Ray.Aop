<?php
namespace Ray\Aop;

class FakeInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();

        return $result;
    }
}
