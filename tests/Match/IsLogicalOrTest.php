<?php

namespace Ray\Aop\Match;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMatcher;

class IsLogicalOrTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalOr)->matchesClass($class, [new FakeMatcher, new FakeMatcher(false)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassFalse()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalOr)->matchesClass($class, [new FakeMatcher, new FakeMatcher(false)]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesClassThreeConditions()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsLogicalOr)->matchesClass($class, [new FakeMatcher(false), new FakeMatcher(false), new FakeMatcher]);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new IsLogicalOr)->matchesMethod($method, [new FakeMatcher, new FakeMatcher]);

        $this->assertTrue($isMatched);
    }
}
