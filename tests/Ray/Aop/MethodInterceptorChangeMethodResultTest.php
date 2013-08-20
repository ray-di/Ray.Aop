<?php

namespace Ray\Aop;

use Ray\Aop\Interceptor\DoubleInterceptor;

/**
 * Test class for Ray.Aop
 */
class MethodInterceptorChangeMethodResultTest extends \PHPUnit_Framework_TestCase
{
    protected $invocation;

    protected $mock;

    protected $interceptor;

    /**
     * target method is:
     *
     * $mock = new Mock;
     * $mock->add(2);
     */
    protected function setUp()
    {
        $this->mock = new MockMethod;
        $this->interceptor = new DoubleInterceptor;
        $target = array($this->mock, 'getDouble');
        $args = array(2);
        $this->invocation = new ReflectiveMethodInvocation($target, $args);
    }

    public function test_invoke()
    {
        $actual = $this->interceptor->invoke($this->invocation);
        $expect = 8;
        $this->assertSame($expect, $actual);
    }

    public function test_invoke_with_interceptors()
    {
        $interceptors = array(new DoubleInterceptor, new DoubleInterceptor);
        $target = array($this->mock, 'getDouble');
        $args = array(2);
        $this->invocation = new ReflectiveMethodInvocation($target, $args, $interceptors);
        $actual = $this->interceptor->invoke($this->invocation);
        $expect = 32;
        $this->assertSame($expect, $actual);
    }

    public function test_invoke_with_double_interceptors()
    {
        $interceptors = array(new DoubleInterceptor, new DoubleInterceptor);
        $target = array($this->mock, 'getDouble');
        $args = array(2);
        $invocation = new ReflectiveMethodInvocation($target, $args, $interceptors);
        $actual = $invocation->proceed();
        $this->assertSame(16, $actual);
    }
}
