<?php

namespace Ray\Aop;

use Ray\Aop\FakeMarker;
use Ray\Aop\FakeClass;

class ReflectiveMethodInvocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectiveMethodInvocation
     */
    protected $invocation;

    /**
     * @var FakeClass
     */
    protected $mock;

    protected function setUp()
    {
        parent::setUp();
        $this->mock = new FakeClass;
        // this is same as $this->mock->add(1);
        $callable = [$this->mock, 'add'];
        $args = [1];
        $this->invocation = new ReflectiveMethodInvocation($callable, $args);
    }

    public function testNew()
    {
        $actual = $this->invocation;
        $this->assertInstanceOf('\Ray\Aop\ReflectiveMethodInvocation', $actual);
    }

    public function testGetMethod()
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertInstanceOf('\ReflectionMethod', $methodReflection);
    }

    public function testGetMethodMethodName()
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertSame(FakeClass::class, $methodReflection->class);
        $this->assertSame('add', $methodReflection->name);
    }

    public function testGetArguments()
    {
        $args = $this->invocation->getArguments();
        $this->assertSame((array)$args, [1]);
    }

    public function testProceed()
    {
        $this->invocation->proceed();
        $this->assertSame(1, $this->mock->a);
    }

    public function testProceedTwoTimes()
    {
        $this->invocation->proceed();
        $this->invocation->proceed();
        $this->assertSame(2, $this->mock->a);
    }

    public function testGetThis()
    {
        $actual = $this->invocation->getThis();
        $this->assertSame($this->mock, $actual);
    }
}
