<?php

namespace Ray\Aop\Sample;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Timer start\n";
        $mTime = microtime(true);
        $result = $invocation->proceed();
        $time = microtime(true) - $mTime;
        echo "Time stop, time is =[" . sprintf('%01.6f', $time) . "] sec\n\n";
    }
}
