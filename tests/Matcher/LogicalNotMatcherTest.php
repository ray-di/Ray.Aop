<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\AnnotatedMatcher;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;
use ReflectionClass;
use ReflectionMethod;

class LogicalNotMatcherTest extends TestCase
{
    public function testMatchesClass(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalNotMatcher())->matchesClass($class, [new FakeMatcher(false)]);
        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse(): void
    {
        $class = new ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalNotMatcher())->matchesClass($class, [new FakeMatcher()]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod(): void
    {
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalNotMatcher())->matchesMethod($method, [new FakeMatcher()]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethodWithRealMatcher(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $method = new ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalNotMatcher())->matchesMethod($method, [$matcher]);
        $this->assertFalse($isMatched);
    }
}
