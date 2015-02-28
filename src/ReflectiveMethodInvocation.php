<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
    public function getMethod()
    {
        if ($this->object instanceof WeavedInterface) {
            return (new \ReflectionObject($this->object))->getParentClass()->getMethod($this->method->getName());
        }

        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->arguments;
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
