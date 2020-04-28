<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidMatcherException;

class BuiltInMatcherTest extends TestCase
{
    /**
     * @var BuiltinMatcher
     */
    private $matcher;

    protected function setUp() : void
    {
        $this->matcher = new BuiltinMatcher('startsWith', ['Ray']);
    }

    public function testMatchesClass() : void
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = $this->matcher->matchesClass($class, ['Ray\Aop']);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod() : void
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = $this->matcher->matchesMethod($method, ['get']);
        $this->assertTrue($isMatched);
    }

    public function testInvalidBuiltinMatcher() : void
    {
        $this->expectException(InvalidMatcherException::class);
        new BuiltinMatcher('invalid', []);
    }
}
