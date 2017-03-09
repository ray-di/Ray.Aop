<?php
namespace Ray\Aop;

class FakeOnionInterceptor2 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
