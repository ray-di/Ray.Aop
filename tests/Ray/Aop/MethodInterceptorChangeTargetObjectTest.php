<?php

namespace Ray\Aop;

/**
 * Test class for Ray.Aop
 */
class MethodInterceptorChangeTargetObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $invocation;

    protected $interceptor;

    protected $mock;

    /**
     * target method is:
     *
     * $mock = new Mock;
     * $mock->add(2);
     */
    protected function setUp()
    {
        $this->interceptor = new MockMethodInterceptor;
        $this->mock = new MockMethod;
        $this->invocation = new ReflectiveMethodInvocation(array($this->mock, 'add'), array(2));
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
