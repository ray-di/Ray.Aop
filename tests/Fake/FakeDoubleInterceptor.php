<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeDoubleInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();

        return $result * 2;
    }
}
