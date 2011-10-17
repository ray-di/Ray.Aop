<?php
/**
 * Ray
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
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
    protected $object;
    protected $args;
    protected $method;
    protected $interceptors;
    protected $interceptorIndex;

    /**
     * @param Calalble $target
     * @param array $args
     * @param array $interceptors
     */
    public function __construct($target, $args, array $interceptors = array())
    {
        $this->object = $target[0];
        $this->args = $args;
        $this->method = new \ReflectionMethod($target[0], $target[1]);
        $this->interceptors = $interceptors;
    }

    /**
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

    // 	public function getThis()
    // 	{
    //  }
}