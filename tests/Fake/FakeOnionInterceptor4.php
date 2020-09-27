<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeOnionInterceptor4 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
