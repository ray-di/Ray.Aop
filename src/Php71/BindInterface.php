<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Php71;

use Ray\Aop\MethodInterceptor;

interface BindInterface
{
    /**
     * Bind pointcuts
     *
     * @param string $class
     * @param array  $pointcuts
     *
     * @return mixed
     */
    public function bind(string $class, array $pointcuts);

    /**
     * Bind interceptors to method
     *
     * @param string              $method
     * @param MethodInterceptor[] $interceptors
     *
     * @return $this
     */
    public function bindInterceptors(string $method, array $interceptors) : BindInterface;

    /**
     * Return bindings data
     *
     * [$methodNameA => [$interceptorA, ...][]
     *
     * @return array
     */
    public function getBindings() : array;

    /**
     * Return hash
     *
     * @param string $salt
     *
     * @return string
     */
    public function toString(string $salt) : string;
}
