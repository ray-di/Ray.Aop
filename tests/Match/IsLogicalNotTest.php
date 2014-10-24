<?php

namespace Ray\Aop\Match;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;

class IsLogicalNotTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalNot)->matchesClass($class, [new FakeMatcher(false)]);
        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalNot)->matchesClass($class, [new FakeMatcher]);
        $this->assertFalse($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new IsLogicalNot)->matchesMethod($method, [new FakeMatcher]);
        $this->assertFalse($isMatched);
    }
}
