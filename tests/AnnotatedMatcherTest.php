<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;
use ReflectionClass;
use ReflectionMethod;

use function serialize;
use function unserialize;

class AnnotatedMatcherTest extends TestCase
{
    public function testMatcherCanBeSerialized(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $this->assertInstanceOf(AnnotatedMatcher::class, unserialize(serialize($matcher)));
    }

    public function testMatchesAnnotatedMethodWithStandardReflectionMethod(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $result = $matcher->matchesMethod($method, [FakeMarker::class]);
        $this->assertTrue($result);
    }

    public function testMatchesAnnotatedMethodWithRayReflectionMethod(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $method = new \Ray\Aop\ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $result = $matcher->matchesMethod($method, [FakeMarker::class]);
        $this->assertTrue($result);
    }

    public function testMatchesAnnotatedClassWithStandardReflectionClass(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeClassAnnotation::class]);
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $result = $matcher->matchesClass($class, [FakeClassAnnotation::class]);
        $this->assertTrue($result);
    }

    public function testMatchesAnnotatedClassWithRayReflectionClass(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeClassAnnotation::class]);
        $class = new \Ray\Aop\ReflectionClass(FakeAnnotateClass::class);
        $result = $matcher->matchesClass($class, [FakeClassAnnotation::class]);
        $this->assertTrue($result);
    }
}
