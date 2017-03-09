<?php
namespace Ray\Aop\Matcher;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeResource;

class AnyMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new AnyMatcher)->matchesClass($class, [FakeResource::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new AnyMatcher)->matchesMethod($method, []);

        $this->assertTrue($isMatched);
    }
}
