<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

final class ReflectiveMethodInvocation implements MethodInvocation
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    /**
     * @var MethodInterceptor[]
     */
    private $interceptors;

    /**
     * @param object              $object
     * @param \ReflectionMethod   $method
     * @param Arguments           $arguments
     * @param MethodInterceptor[] $interceptors
     */
    public function __construct(
        $object,
        \ReflectionMethod $method,
        Arguments $arguments,
        array $interceptors = []
    ) {
        $this->object = $object;
        $this->method = $method;
        $this->arguments = $arguments;
        $this->interceptors = $interceptors;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : \ReflectionMethod
    {
        if ($this->object instanceof WeavedInterface) {
            $class = (new \ReflectionObject($this->object))->getParentClass();
            $method = new ReflectionMethod($class->name, $this->method->name);
            $method->setObject($this->object, $this->method);

            return $method;
        }

        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments() : \ArrayObject
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
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
        if ($this->interceptors === []) {
            return $this->method->invokeArgs($this->object, $this->arguments->getArrayCopy());
        }
        $interceptor = array_shift($this->interceptors);
        /* @var $interceptor MethodInterceptor */

        return $interceptor->invoke($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getThis()
    {
        return $this->object;
    }
}
