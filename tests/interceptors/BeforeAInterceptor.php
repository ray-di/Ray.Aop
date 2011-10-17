<?php
namespace Ray\Aop;

class BeforeAInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        $invocation->
        $result = $invocation->proceed();
        return $result * 2;
    }
}