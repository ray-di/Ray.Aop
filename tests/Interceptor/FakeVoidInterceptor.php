<?php

namespace Ray\Aop;

class FakeVoidInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();

        return $result;
    }
}
