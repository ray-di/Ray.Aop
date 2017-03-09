<?php
namespace Ray\Aop;

class FakeMethodInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->proceed();
    }
}
