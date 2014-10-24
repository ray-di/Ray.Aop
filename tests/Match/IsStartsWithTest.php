<?php

namespace Ray\Aop\Match;

use Ray\Aop\FakeClass;

class IsStartsWithTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = (new IsStartsWith)->matchesClass($class, ['Ray\Aop']);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new IsStartsWith)->matchesMethod($method, ['get']);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodNotMatch()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new IsStartsWith)->matchesMethod($method, ['xxx']);

        $this->assertFalse($isMatched);
    }
}
