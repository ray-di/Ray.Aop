<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;

class LogicalOrMatcherTest extends TestCase
{
    public function testMatchesClass() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalOrMatcher)->matchesClass($class, [new FakeMatcher, new FakeMatcher(false)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalOrMatcher)->matchesClass($class, [new FakeMatcher, new FakeMatcher(false)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassThreeConditions() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalOrMatcher)->matchesClass($class, [new FakeMatcher(false), new FakeMatcher(false), new FakeMatcher]);
        $this->assertTrue($isMatched);
    }

    public function testLogicalOrNotMatch() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalOrMatcher)->matchesClass($class, [new FakeMatcher(true, false), new FakeMatcher(true, false), new FakeMatcher(true, false)]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod() : void
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalOrMatcher)->matchesMethod($method, [new FakeMatcher, new FakeMatcher]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodFalse() : void
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalOrMatcher)->matchesMethod($method, [new FakeMatcher(false), new FakeMatcher(false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesMethodMoreThanTwoMatch() : void
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalOrMatcher)->matchesMethod($method, [new FakeMatcher(false), new FakeMatcher(false), new FakeMatcher(false), new FakeMatcher(true)]);

        $this->assertTrue($isMatched);
    }
}
