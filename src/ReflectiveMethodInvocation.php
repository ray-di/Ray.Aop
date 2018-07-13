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
     * @var callable
     */
    private $callable;

    /**
     * @param object              $object
     * @param string              $method
     * @param array               $arguments
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
        $callable = [$object, $method];
        if (is_callable($callable)) {
            $this->callable = $callable;
        }
        $this->arguments = $arguments;
        $this->interceptors = $interceptors;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : ReflectionMethod
    {
        if ($this->object instanceof WeavedInterface) {
            $class = (new \ReflectionObject($this->object))->getParentClass();
            if (! $class instanceof \ReflectionClass) {
                throw new \LogicException; // @codeCoverageIgnore
            }
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
