<?php
namespace Ray\Aop;

class FakeAbortProceedInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return 20;
    }
}
