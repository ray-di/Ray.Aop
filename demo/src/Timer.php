<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Timer start\n";
        $mTime = microtime(true);
        $invocation->proceed();
        $time = microtime(true) - $mTime;
        echo 'Time stop, time is =[' . sprintf('%01.6f', $time) . "] sec\n\n";
    }
}
