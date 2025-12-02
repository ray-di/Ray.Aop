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

    public function testGetArgumentsReturnsConstructorArguments(): void
    {
        $arguments = ['Ray\Aop'];
        $matcher = new BuiltinMatcher('startsWith', $arguments);
        $this->assertEquals($arguments, $matcher->getArguments());
    }

    public function testMatchesClassWithDifferentMatcher(): void
    {
        $matcher = new BuiltinMatcher('any', []);
        $class = new ReflectionClass(FakeClass::class);
        $this->assertTrue($matcher->matchesClass($class, []));
    }

    public function testMatchesMethodWithDifferentMatcher(): void
    {
        $matcher = new BuiltinMatcher('any', []);
        $method = new ReflectionMethod(FakeClass::class, 'getDouble');
        $this->assertTrue($matcher->matchesMethod($method, []));
    }

    public function testMatcherWithEmptyArguments(): void
    {
        $matcher = new BuiltinMatcher('any', []);
        $this->assertEquals([], $matcher->getArguments());
    }
}
