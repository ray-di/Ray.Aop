<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidMatcherException;
use ReflectionClass;
use ReflectionMethod;

class BuiltInMatcherTest extends TestCase
{
    private BuiltinMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new BuiltinMatcher('startsWith', ['Ray']);
    }

    public function testMatchesClassWithMatchingNamespace(): void
    {
        $class = new ReflectionClass(FakeClass::class);
        $isMatched = $this->matcher->matchesClass($class, ['Ray\Aop']);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethodWithMatchingPrefix(): void
    {
        $method = new ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = $this->matcher->matchesMethod($method, ['get']);
        $this->assertTrue($isMatched);
    }

    public function testInvalidMatcherNameThrowsException(): void
    {
        $this->expectException(InvalidMatcherException::class);
        new BuiltinMatcher('invalid', []);
    }
}
