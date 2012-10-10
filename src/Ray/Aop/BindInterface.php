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
interface BindInterface
{
    /**
     * Bind method to interceptors
     *
     * @param string $method
     * @param array  $interceptors
     * @param object $annotation   Binding annotation if annotate bind
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

    /**
     * Make pointcuts to binding information
     *
     * @param string $class
     * @param array  $pointcuts
     *
     * @return Bind
     */
    public function bind($class, array $pointcuts);
}
