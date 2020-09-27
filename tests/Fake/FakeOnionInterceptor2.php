<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeOnionInterceptor2 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
