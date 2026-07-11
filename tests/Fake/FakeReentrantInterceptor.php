<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * Interceptor that calls another intercepted method on the same object.
 * Used to verify reentrancy safety after _isAspect flag removal.
 */
final class FakeReentrantInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation): mixed
    {
        // When intercepting returnSame, call getSub (also intercepted) on the same object
        if ($invocation->getMethod()->getName() === 'returnSame') {
            $invocation->getThis()->getSub(10, 20);
        }

        return $invocation->proceed();
    }
}
