<?php

namespace Ray\Aop;

class FakeDoubleArgumentInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        $args[0] *= 2;
        $result = $invocation->proceed();

        return $result;
    }
}
