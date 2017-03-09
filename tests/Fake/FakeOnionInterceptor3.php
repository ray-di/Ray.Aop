<?php
namespace Ray\Aop;

class FakeOnionInterceptor3 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
