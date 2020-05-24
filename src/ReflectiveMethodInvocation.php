<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayObject;

final class ReflectiveMethodInvocation implements MethodInvocation
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var \ArrayObject<int, mixed>
     */
    private $arguments;

    /**
     * @var string
     */
    private $method;

    /**
     * @var MethodInterceptor[]
     */
    private $interceptors;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @param array<MethodInterceptor> $interceptors
     * @param array<int, mixed>        $arguments
     */
    public function __construct(
        object $object,
        string $method,
        array $arguments,
        array $interceptors = []
    ) {
        $this->object = $object;
        $this->method = $method;
        $callable = [$object, $method];
        assert(is_callable($callable));
        $this->callable = $callable;
        $this->arguments = new \ArrayObject($arguments);
        $this->interceptors = $interceptors;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : ReflectionMethod
    {
        if ($this->object instanceof WeavedInterface) {
            $class = (new \ReflectionObject($this->object))->getParentClass();
            assert($class instanceof \ReflectionClass);
            $method = new ReflectionMethod($class->name, $this->method);
            $method->setObject($this->object, $method);

            return $method;
        }

        return new ReflectionMethod($this->object, $this->method);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments() : ArrayObject
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedArguments() : ArrayObject
    {
        $args = $this->getArguments();
        $params = $this->getMethod()->getParameters();
        $namedParams = [];
        foreach ($params as $param) {
            /** @psalm-suppress MixedAssignment */
            $namedParams[$param->getName()] = $args[$param->getPosition()];
        }

        return new \ArrayObject($namedParams);
    }

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $interceptor = array_shift($this->interceptors);
        if ($interceptor instanceof MethodInterceptor) {
            return $interceptor->invoke($this);
        }

        return call_user_func_array($this->callable, (array) $this->arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getThis()
    {
        return $this->object;
    }
}
