<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

interface BindInterface
{
    /**
     * Bind matcher to class
     *
     * @param string     $class
     * @param Pointcut[] $pointcuts
     *
     * @return array
     */
    public function bind($class, array $pointcuts);

    /**
     * Bind interceptors to explicit method name
     *
     * @param string        $method
     * @param Interceptor[] $interceptors
s     *
     * @return Bind
     */
    public function bindInterceptors($method, array $interceptors);

    /**
     * Return matched interceptor
     *
     * @param string $name class name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name);
}
