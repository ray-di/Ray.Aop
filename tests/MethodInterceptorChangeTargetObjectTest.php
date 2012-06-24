<?php

namespace Ray\Aop;
require_once __DIR__ . '/MockMethodInterceptor.php';

/**
 * Test class for Ray.Aop
 */
class MethodInterceptorChangeTargetObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $invocation;

    /**
     * taget method is:
     *
     * $mock = new MockMethod;
     * $mock->add(2);
     */
    protected function setUp()
    {
        $this->interceptor = new MockMethodInterceptor;
        $this->mock = new MockMethod;
        $this->invocation = new ReflectiveMethodInvocation(array($this->mock, 'add'), array(2));
    }

    public function test_invoke()
    {
        $this->interceptor->invoke($this->invocation);
        $expect = 2;
        $actual = $this->mock->a;
        $this->assertSame($expect, $actual);
    }

    public function test_invoke_twice()
    {
        $this->interceptor->invoke($this->invocation);
        $this->interceptor->invoke($this->invocation);
        $expect = 4;
        $actual = $this->mock->a;
        $this->assertSame($expect, $actual);
    }
}
