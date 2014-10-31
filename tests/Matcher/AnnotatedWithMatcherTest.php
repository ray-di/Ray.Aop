<?php

namespace Ray\Aop\Matcher;

use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMarker;
use Ray\Aop\FakeResource;

class AnnotatedWithMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new AnnotatedWithMatcher)->matchesClass($class, [FakeResource::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new AnnotatedWithMatcher)->matchesMethod($method, [FakeMarker::class]);

        $this->assertTrue($isMatched);
    }
}
