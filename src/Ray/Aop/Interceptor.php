<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * This interface represents a generic interceptor.
 *
 * This interface is not used directly. Use the the sub-interfaces to intercept specific events.
 *
 * @package Ray.Aop
 * @link    http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/Interceptor.html
 */
interface Interceptor extends Advice
{
}
