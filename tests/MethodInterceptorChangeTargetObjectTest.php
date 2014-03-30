<?php

namespace Ray\Aop;

class MethodInterceptorChangeTargetObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectiveMethodInvocation
     */
    protected $invocation;

    /**
     * @var MockMethodInterceptor
     */
    protected $interceptor;

    /**
     * @var MockMethod
     */
    protected $mock;

    protected function setUp()
    {
        $this->interceptor = new MockMethodInterceptor;
        $this->mock = new MockMethod;
        $this->invocation = new ReflectiveMethodInvocation([$this->mock, 'add'], [2]);
    }

    public function testInvoke()
    {
        $this->interceptor->invoke($this->invocation);
        $expect = 2;
        $actual = $this->mock->a;
        $this->assertSame($expect, $actual);
    }

    public function testInvokeTwice()
    {
        $this->interceptor->invoke($this->invocation);
        $this->interceptor->invoke($this->invocation);
        $expect = 4;
        $actual = $this->mock->a;
        $this->assertSame($expect, $actual);
    }
}
