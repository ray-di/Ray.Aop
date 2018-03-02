<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
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
    public function getArguments() : \ArrayObject;
}
