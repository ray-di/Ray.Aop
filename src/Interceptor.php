<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * This interface represents a generic interceptor.
 *
 * This interface is not used directly. Use the the sub-interfaces to intercept specific events.
 *
 * @see http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/Interceptor.html
 */
interface Interceptor extends Advice
{
}
