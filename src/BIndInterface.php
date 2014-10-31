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
     * Bind pointcuts
     *
     * @return $this
     */
    public function bind($class, array $pointcuts);

    /**
     * Bind interceptors to method
     *
     * @param string              $method
     * @param MethodInterceptor[] $interceptors
     *
     * @return $this
     */
    public function bindInterceptors($method, array $interceptors);

    /**
     * Return bindings data
     *
     * [$methodNameA => [$interceptorA, ...][]
     *
     * @return array
     */
    public function getBindings();

    /**
     * @return string
     */
    public function __toString();
}
