<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeMethodInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->proceed();
    }
}
