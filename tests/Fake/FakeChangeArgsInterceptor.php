<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeChangeArgsInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        $args[0] = 'changed';

        return $invocation->proceed();
    }
}
