<?php
namespace Ray\Aop;

class FakeOnionInterceptor4 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
