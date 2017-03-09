<?php
namespace Ray\Aop;

class FakeOnionInterceptor1 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
