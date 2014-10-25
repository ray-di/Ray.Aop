<?php

namespace Ray\Aop\Matcher;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;

class LogicalNotMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalNotMatcher)->matchesClass($class, [new FakeMatcher(false)]);
        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new LogicalNotMatcher)->matchesClass($class, [new FakeMatcher]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new LogicalNotMatcher)->matchesMethod($method, [new FakeMatcher]);
        $this->assertFalse($isMatched);
    }
}
