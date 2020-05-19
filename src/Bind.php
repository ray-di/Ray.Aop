<?php

declare(strict_types=1);

namespace Ray\Aop;

final class Bind implements BindInterface
{
    /**
     * @var array<string, array<MethodInterceptor>>
     */
    private $bindings = [];

    /**
     * @var MethodMatch
     */
    private $methodMatch;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        $this->methodMatch = new MethodMatch($this);
    }

    public function __sleep()
    {
        return ['bindings'];
    }

    /**
     * {@inheritdoc}
     */
    public function bind(string $class, array $pointcuts) : BindInterface
    {
        $pointcuts = $this->getAnnotationPointcuts($pointcuts);
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            ($this->methodMatch)($class, $method, $pointcuts);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function bindInterceptors(string $method, array $interceptors) : BindInterface
    {
        $this->bindings[$method] = ! array_key_exists($method, $this->bindings) ? $interceptors : array_merge(
            $this->bindings[$method],
            $interceptors
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings() : array
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($salt) : string
    {
        unset($salt);

        return serialize($this->bindings);
    }

    /**
     * @param Pointcut[] $pointcuts
     *
     * @return Pointcut[]
     */
    public function getAnnotationPointcuts(array &$pointcuts) : array
    {
        $keyPointcuts = [];
        foreach ($pointcuts as $key => $pointcut) {
            if ($pointcut->methodMatcher instanceof AnnotatedMatcher) {
                $key = $pointcut->methodMatcher->annotation;
            }
            $keyPointcuts[$key] = $pointcut;
        }

        return $keyPointcuts;
    }
}
