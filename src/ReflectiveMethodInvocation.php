<?php

declare(strict_types=1);

namespace Ray\Aop;

final class ReflectiveMethodInvocation implements MethodInvocation
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var array|\ArrayObject
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
     * @var array
     */
    private $callable;

    /**
     * @param object              $object
     * @param MethodInterceptor[] $interceptors
     */
    public function __construct(
        $object,
        string $method,
        array $arguments,
        array $interceptors = []
    ) {
        $this->object = $object;
        $this->method = $method;
        $this->callable = [$object, $method];
        $this->arguments = $arguments;
        $this->interceptors = $interceptors;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
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
    public function getArguments() : \ArrayObject
    {
        $this->arguments = new \ArrayObject($this->arguments);

        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function getNamedArguments() : \ArrayObject
    {
        $args = $this->getArguments();
        $paramas = $this->getMethod()->getParameters();
        $namedParams = new \ArrayObject;
        foreach ($paramas as $param) {
            $namedParams[$param->getName()] = $args[$param->getPosition()];
        }

        return $namedParams;
    }

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        if ($this->interceptors === [] && \is_callable($this->callable)) {
            return call_user_func_array($this->callable, (array) $this->arguments);
        }
        $interceptor = array_shift($this->interceptors);
        if ($interceptor instanceof MethodInterceptor) {
            return $interceptor->invoke($this);
        }

        throw new \LogicException;
    }

    /**
     * {@inheritdoc}
     */
    public function getThis()
    {
        return $this->object;
    }
}
