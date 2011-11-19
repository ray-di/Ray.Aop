<?php
/**
 * Ray
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Intercepts calls on an interface on its way to the target. These are nested "on top" of the target.
 *
 * The user should implement the invoke(MethodInvocation) method to modify the original behavior.
 * E.g. the following class implements a tracing interceptor (traces all the calls on the intercepted method(s)):
 *
 * @package Ray.Aop
 * @link http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html
 */
interface MethodInterceptor extends Inpterceptor
{
    /**
     * Implement this method to perform extra treatments before and
     * after the invocation. Polite implementations would certainly
     * like to invoke {@link Joinpoint#proceed()}.
     *
     * @param MethodInvocation $invocation the method invocation joinpoint
     *
     * @return the result of the call to {@link
     * Joinpoint#proceed()}, might be intercepted by the
     * interceptor.
     *
     * @throws \Ray\Aop\Exception
     */
    public function invoke(MethodInvocation $invocation);
}