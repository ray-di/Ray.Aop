<?php

namespace Ray\Aop\Match;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMarker;
use Ray\Aop\FakeMatcher;

class IsLogicalAndTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalAnd)->matchesClass($class, [new FakeMatcher, new FakeMatcher]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalAnd)->matchesClass($class, [new FakeMatcher, new FakeMatcher(false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesClassThreeConditions()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalAnd)->matchesClass($class, [new FakeMatcher, new FakeMatcher, new FakeMatcher(false)]);

        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new IsLogicalAnd)->matchesMethod($method, [new FakeMatcher, new FakeMatcher]);

        $this->assertTrue($isMatched);
    }
}
