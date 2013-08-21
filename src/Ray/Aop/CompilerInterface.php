<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 */
interface CompilerInterface
{
    /**
     * Return new aspect weaved object instance
     *
     * @param string $class
     * @param array  $args
     * @param Bind   $bind
     *
     * @return object
     */
    public function newInstance($class, array $args =[], Bind $bind);
}
