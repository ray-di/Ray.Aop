<?php
namespace Ray\Aop;

/**
 * Test class for Ray.Aop
 */
class MockMethodInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->proceed();
    }
}
