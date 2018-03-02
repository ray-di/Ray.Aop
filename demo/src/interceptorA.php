<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Demo;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class interceptorA implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "before A\n";
        $invocation->proceed();
        echo "after A\n";
    }
}
