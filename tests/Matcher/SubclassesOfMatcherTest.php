<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\FakeClass;
use ReflectionClass;
use ReflectionMethod;

class SubclassesOfMatcherTest extends TestCase
{
    public function testMatchesClass(): void
    {
        $class = new ReflectionClass(FakeClass::class);
        $isMatched = (new SubclassesOfMatcher())->matchesClass($class, [FakeClass::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod(): void
    {
        $this->expectException(InvalidAnnotationException::class);

        $method = new ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new SubclassesOfMatcher())->matchesMethod($method, ['get']);

        $this->assertTrue($isMatched);
    }
}
