<?php

declare(strict_types=1);

namespace Ray\Aop;

interface BindInterface
{
    /**
     * Bind pointcuts
     *
     * @param string     $class     class name
     * @param Pointcut[] $pointcuts Pointcut array
     */
    public function bind(string $class, array $pointcuts);

    /**
     * Bind interceptors to method
     *
     * @param MethodInterceptor[] $interceptors
     *
     * @return BindInterface
     */
    public function bindInterceptors(string $method, array $interceptors);

    /**
     * Return bindings data
     *
     * [$methodNameA => [$interceptorA, ...][]
     *
     * @return array
     */
    public function getBindings();

    /**
     * Return hash
     *
     * @param string $salt
     *
     * @return string
     */
    public function toString($salt);
}
