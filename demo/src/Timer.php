<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo 'Timer start' . PHP_EOL;
        $mTime = microtime(true);
        $invocation->proceed();
        $time = microtime(true) - $mTime;
        echo sprintf('Time stop, time is =[%01.6f] sec', $time) . PHP_EOL;
    }
}
