<?php

namespace Ray\Aop;

use Ray\Aop\Match\IsSubclassesOf;
use Ray\Aop\Exception\InvalidMatcher;

class IsSubclassesOfTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = (new IsSubclassesOf)->matchesClass($class, [FakeClass::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $this->setExpectedException(InvalidMatcher::class);
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new IsSubclassesOf)->matchesMethod($method, ['get']);
    }
}
