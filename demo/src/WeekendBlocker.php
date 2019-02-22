<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
                $invocation->getMethod()->getName() . ' not allowed on weekends!'
            );
        }

        return $invocation->proceed();
    }
}
