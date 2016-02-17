<?php

namespace Ray\Aop;

class ReflectiveMethodInvocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReflectiveMethodInvocation
     */
    protected $invocation;

    /**
     * @var FakeClass
     */
    protected $fake;

    protected function setUp()
    {
        parent::setUp();
        $this->fake = new FakeClass;
        $this->invocation = new ReflectiveMethodInvocation($this->fake, new \ReflectionMethod($this->fake, 'add'), new Arguments([1]));
    }

    public function testGetMethod()
    {
        $methodReflection = $this->invocation->getMethod();
        $this->assertInstanceOf(\ReflectionMethod::class, $methodReflection);
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
        $this->assertSame((array) $args, [1]);
    }

    public function testProceed()
    {
        $this->invocation->proceed();
        $this->assertSame(1, $this->fake->a);
    }

    public function testProceedTwoTimes()
    {
        $this->invocation->proceed();
        $this->invocation->proceed();
        $this->assertSame(2, $this->fake->a);
    }

    public function testGetThis()
    {
        $actual = $this->invocation->getThis();
        $this->assertSame($this->fake, $actual);
    }

    public function testGetParentMethod()
    {
        $fake = new FakeWeavedClass;
        $invocation = new ReflectiveMethodInvocation($fake, new \ReflectionMethod($fake, 'add'), new Arguments([1]));
        $method = $invocation->getMethod();
        $this->assertSame(FakeClass::class, $method->class);
        $this->assertSame('add', $method->name);
    }

    public function testProceedMultipleInterceptors()
    {
        $fake = new FakeWeavedClass;
        $invocation = new ReflectiveMethodInvocation($fake, new \ReflectionMethod($fake, 'add'), new Arguments([1]), [new FakeInterceptor, new FakeInterceptor]);
        $invocation->proceed();
        $this->assertSame(1, $fake->a);
    }
}
