<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $mtime = microtime(true);
        $result = $invocation->proceed();
        $time = microtime(true) - $mtime;
        return "$result timer[$time] sec";
    }
}