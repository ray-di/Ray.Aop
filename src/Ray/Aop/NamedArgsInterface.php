<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\MethodInvocation;

/**
 * Interface for named parameter in interceptor
 *
 * @package    Ray.Aop
 */
interface NamedArgsInterface
{
    /**
     * @param MethodInvocation $invocation
     *
     * @return array
     */
    public function get(MethodInvocation $invocation);
}
