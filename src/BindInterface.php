<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Bind method name to interceptors
 */
interface BindInterface
{
    /**
     * Make pointcuts to binding information
     *
     * @param string $class
     * @param array  $pointcuts
     *
     * @return Bind
     */
    public function bind($class, array $pointcuts);

    /**
     * Bind method to interceptors
     *
     * @param string $method
     * @param array  $interceptors
     * @param object $annotation
     *
     * @return Bind
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null);

    /**
     * Get matched Interceptor
     *
     * @param string $name class name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name);
}
