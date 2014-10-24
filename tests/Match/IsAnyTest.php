<?php

namespace Ray\Aop\Match;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeResource;

class IsAnyTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new IsAny)->matchesClass($class, [FakeResource::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new IsAny)->matchesMethod($method, []);

        $this->assertTrue($isMatched);
    }
}
