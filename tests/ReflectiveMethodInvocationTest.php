<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeClassMarker;
use Ray\Aop\Annotation\FakeMarker;

class ReflectiveMethodInvocationTest extends TestCase
{
    /**
     * @var ReflectiveMethodInvocation
     */
    protected $invocation;

    /**
     * @var FakeClass
     */
    protected $fake;

    protected function setUp() : void
    {
        parent::setUp();
        $this->fake = new FakeClass;
        $this->invocation = new ReflectiveMethodInvocation($this->fake, 'add', [1]);
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
        $invocation = new ReflectiveMethodInvocation($fake, 'add', [1]);
        $method = $invocation->getMethod();
        $this->assertSame(FakeClass::class, $method->class);
        $this->assertSame('add', $method->name);
    }

    public function testProceedMultipleInterceptors()
    {
        $fake = new FakeWeavedClass;
        $invocation = new ReflectiveMethodInvocation($fake, 'add', [1], [new FakeInterceptor, new FakeInterceptor]);
        $invocation->proceed();
        $this->assertSame(1, $fake->a);
    }

    public function testGetNamedArguments()
    {
        $args = $this->invocation->getNamedArguments();
        $this->assertSame((array) $args, ['n' => 1]);
    }

    public function testNoInterceptor()
    {
        $this->expectException(\LogicException::class);
        $invalidIntercepor = new \stdClass;
        $invocation = new ReflectiveMethodInvocation(new FakeWeavedClass, 'add', [1], [$invalidIntercepor]);
        $invocation->proceed();
    }

    public function testGetAnnotation()
    {
        $fakeMarker = $this->invocation->getMethod()->getAnnotation(FakeMarker::class);
        $this->assertInstanceOf(FakeMarker::class, $fakeMarker);
    }

    public function testGetClassAnnotati()
    {
        $fakeMarker = $this->invocation->getMethod()->getDeclaringClass()->getAnnotation(FakeClassMarker::class);
        $this->assertInstanceOf(FakeClassMarker::class, $fakeMarker);
    }
}
