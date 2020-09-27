<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeAbortProceedInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return 20;
    }
}
