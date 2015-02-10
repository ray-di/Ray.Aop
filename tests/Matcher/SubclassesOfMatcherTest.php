<?php

namespace Ray\Aop\Matcher;

use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\FakeClass;

class SubclassesOfMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = (new SubclassesOfMatcher)->matchesClass($class, [FakeClass::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $this->setExpectedException(InvalidAnnotationException::class);
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new SubclassesOfMatcher)->matchesMethod($method, ['get']);

        $this->assertTrue($isMatched);
    }
}
