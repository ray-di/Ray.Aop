<?php

namespace Ray\Aop\Matcher;

use Ray\Aop\FakeClass;

class StartsWithMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = (new StartsWithMatcher)->matchesClass($class, ['Ray\Aop']);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new StartsWithMatcher)->matchesMethod($method, ['get']);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodNotMatch()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new StartsWithMatcher)->matchesMethod($method, ['xxx']);

        $this->assertFalse($isMatched);
    }
}
