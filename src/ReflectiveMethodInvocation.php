<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ArrayObject;
use ReflectionMethod;

/**
 * Ray's implementation of AOP Alliance MethodInvocation interface.
 */
class ReflectiveMethodInvocation implements MethodInvocation
{
    /**
     * Object
     *
     * @var object
     */
    protected $object;

    /**
     * Parameters
     *
     * @var ArrayObject
     */
    protected $args;

    /**
     * Current method
     *
     * @var \ReflectionMethod
     */
    protected $method;

    /**
     * Interceptors
     *
     * @var array
     */
    protected $interceptors;

    /**
     * Interceptor index
     *
     * @var integer
     */
    protected $interceptorIndex;

    /**
     * @param Callable $target
     * @param array    $args
     * @param array    $interceptors
     * @param mixed    $annotation
     */
    public function __construct(callable $target, array $args, array $interceptors = [])
    {
        $this->method = new ReflectionMethod($target[0], $target[1]);
        $this->object = $target[0];
        $this->args = new ArrayObject($args);
        $this->interceptors = $interceptors;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return ($this->object instanceof WeavedInterface) ?
            (new \ReflectionObject($this->object))->getParentClass()->getMethod($this->method->name) :
            $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->args;
    }

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        if ($this->interceptors === array()) {
            return $this->method->invokeArgs($this->object, $this->args->getArrayCopy());
        }
        $interceptor = array_shift($this->interceptors);

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
