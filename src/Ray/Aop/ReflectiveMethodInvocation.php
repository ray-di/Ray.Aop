<?php
/**
 * Ray
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\MethodInvocation;

/**
 * Ray's implementation of AOP Alliance MethodInvocation interface.
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
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
     * @var array
     */
    protected $args;

    /**
     * Method
     *
     * @var string
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
     * Constructor
     *
     * @param object $target \Callable
     * @param array  $args
     * @param array  $interceptors
     */
    public function __construct($target, array $args, array $interceptors = [], $annotation = [])
    {
        $this->object = $target[0];
        $this->args = $args;
        $this->method = new \ReflectionMethod($target[0], $target[1]);
        $this->interceptors = $interceptors;
        $this->annotation = $annotation;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInvocation::getMethod()
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.Invocation::getArguments()
     */
    public function getArguments()
    {
        return $this->args;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.Joinpoint::proceed()
     */
    public function proceed()
    {
        if ($this->interceptors === array()) {
            return $this->method->invokeArgs($this->object, $this->args);
        }
        $interceptor = array_shift($this->interceptors);
        return $interceptor->invoke($this);
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.Joinpoint::getThis()
     */
    public function getThis()
    {
        return $this->object;
    }

    /**
     * Return method annotation
     *
     * @return object Annotation
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}