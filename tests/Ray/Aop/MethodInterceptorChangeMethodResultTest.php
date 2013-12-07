<?php

namespace Ray\Aop;

use Ray\Aop\Interceptor\DoubleInterceptor;

class MethodInterceptorChangeMethodResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockMethod
     */
    protected $mock;

    /**
     * @var ReflectiveMethodInvocation
     */
    protected $invocation;

    /**
     * @var DoubleInterceptor
     */
    protected $interceptor;

    protected function setUp()
    {
        $this->mock = new MockMethod;
        $this->interceptor = new DoubleInterceptor;
        $target = [$this->mock, 'getDouble'];
        $args = [2];
        $this->invocation = new ReflectiveMethodInvocation($target, $args);
    }

    public function testInvoke()
    {
        $actual = $this->interceptor->invoke($this->invocation);
        $expect = 8;
        $this->assertSame($expect, $actual);
    }

    public function testInvokeWithInterceptors()
    {
        $interceptors = [new DoubleInterceptor, new DoubleInterceptor];
        $target = [$this->mock, 'getDouble'];
        $args = [2];
        $invocation = new ReflectiveMethodInvocation($target, $args, $interceptors);
        $actual = $this->interceptor->invoke($invocation);
        $expect = 32;
        $this->assertSame($expect, $actual);
    }

    public function testInvokeWithDoubleInterceptors()
    {
        $interceptors = [new DoubleInterceptor, new DoubleInterceptor];
        $target = [$this->mock, 'getDouble'];
        $args = [2];
        $invocation = new ReflectiveMethodInvocation($target, $args, $interceptors);
        $actual = $invocation->proceed();
        $this->assertSame(16, $actual);
    }

}
