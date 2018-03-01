<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

/**
 * Description of an invocation to a method, given to an interceptor
 * upon method-call.
 *
 * <p>A method invocation is a joinpoint and can be intercepted by a method
 * interceptor.
 *
 * @see MethodInterceptor
 * http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInvocation.html
 */
interface MethodInvocation extends Invocation
{
    /**
     * Gets the method being called.
     *
     * <p>This method is a friendly implementation of the {@link * Joinpoint#getStaticPart()} method (same result).
     *
     * @return \ReflectionMethod|ReflectionMethod method being called
     */
    public function getMethod() : \ReflectionMethod;
}
