<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\AnnotatedMatcher;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;
use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;

class LogicalAndMatcherTest extends TestCase
{
    public function testMatchesClass(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher())->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, true)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher())->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesClassThreeConditions(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher())->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, true), new FakeMatcher(true, false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod(): void
    {
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher())->matchesMethod($method, [new FakeMatcher(true, true), new FakeMatcher(true, true)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodWithRealMatcher(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher())->matchesMethod($method, [$matcher, new FakeMatcher(true)]);
        $this->assertTrue($isMatched);
    }

    public function testMatchesClassWithEmptyArguments(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher())->matchesClass($class, []);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodWithEmptyArguments(): void
    {
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher())->matchesMethod($method, []);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodFalse(): void
    {
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher())->matchesMethod($method, [new FakeMatcher(true, true), new FakeMatcher(true, false)]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethodFirstConditionFalse(): void
    {
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher())->matchesMethod($method, [new FakeMatcher(true, false), new FakeMatcher(true, true)]);
        $this->assertFalse($isMatched);
    }
}
