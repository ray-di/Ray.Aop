<?php

declare(strict_types=1);

class FakeGlobalInterceptor implements Ray\Aop\MethodInterceptor
{
    public function invoke(Ray\Aop\MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}
