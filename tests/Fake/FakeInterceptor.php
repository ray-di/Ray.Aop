<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
