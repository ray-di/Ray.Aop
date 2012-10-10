<?php

namespace Ray\Aop;

use InvalidArgumentException;

/**
 * Test class for Ray.Aop
 */
class ReflectiveMethodInvocationTest extends \PHPUnit_Framework_TestCase
{
    protected $invocation;

    protected function setUp()
    {
        parent::setUp();
        $this->mock = new MockMethod;
        // this is same as $this->mock->add(1);
        $callable = array($this->mock, 'add');
        $args = array(1);
        $this->invocation = new ReflectiveMethodInvocation($callable, $args);
    }

    public function test_New()
    {
        $actual = $this->invocation;
        $this->assertInstanceOf('\Ray\Aop\ReflectiveMethodInvocation', $this->invocation);
    }

    public function test_getMethod()
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertInstanceOf('\ReflectionMethod', $methodReflection);

    }

    public function test_getMethodMethodName()
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertSame('Ray\Aop\MockMethod', $methodReflection->class);
        $this->assertSame('add', $methodReflection->name);
    }

    public function test_getArguments()
    {
        $args = $this->invocation->getArguments();
        $this->assertSame($args, array(1));
    }

    public function test_proceed()
    {
        $this->invocation->proceed();
        $this->assertSame(1, $this->mock->a);
    }

    public function test_proceedTwoTimes()
    {
        $this->invocation->proceed();
        $this->invocation->proceed();
        $this->assertSame(2, $this->mock->a);
    }

    public function test_getThis()
    {
        $actual = $this->invocation->getThis();
        $this->assertSame($this->mock, $actual);
    }

    public function test_getAnnotation()
    {
        $mock = new MockMethod;
        $callable = array($mock, 'add');
        $invocation = new ReflectiveMethodInvocation($callable, [], [], new \Ray\Aop\Tests\Annotation\Marker);
        $annotations = $invocation->getAnnotation();
        $this->assertInstanceOf('Ray\Aop\Tests\Annotation\Marker', $annotations);
    }
}
