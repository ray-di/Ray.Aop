<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * This interface represents an invocation in the program.
 *
 * An invocation is a joinpoint and can be intercepted by an interceptor.
 *
 * @link http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/Invocation.html
 */
interface Invocation extends Joinpoint
{

    /**
     * Get the arguments as an array object.
     *
     * It is possible to change element values within this
     * array to change the arguments.
     *
     * @return \ArrayObject the argument of the invocation
     */
    public function getArguments();
}
