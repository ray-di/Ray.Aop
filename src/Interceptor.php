<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

/**
 * This interface represents a generic interceptor.
 *
 * This interface is not used directly. Use the the sub-interfaces to intercept specific events.
 *
 * @link http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/Interceptor.html
 */
interface Interceptor extends Advice
{
}
