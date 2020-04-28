<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeResource;

class AnnotatedWithMatcherTest extends TestCase
{
    public function testMatchesClass() : void
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new AnnotatedWithMatcher)->matchesClass($class, [FakeResource::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchMethod() : void
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new AnnotatedWithMatcher)->matchesMethod($method, [FakeMarker::class]);

        $this->assertTrue($isMatched);
    }
}
