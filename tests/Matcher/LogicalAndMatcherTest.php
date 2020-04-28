<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;

class LogicalAndMatcherTest extends TestCase
{
    public function testMatchesClass() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher)->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, true)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher)->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesClassThreeConditions() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalAndMatcher)->matchesClass($class, [new FakeMatcher(true, true), new FakeMatcher(true, true), new FakeMatcher(true, false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod() : void
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalAndMatcher)->matchesMethod($method, [new FakeMatcher(true, true), new FakeMatcher(true, true)]);

        $this->assertTrue($isMatched);
    }
}
