<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidMatcherException;

class BuiltInMatcherTest extends TestCase
{
    /**
     * @var BuiltinMatcher
     */
    private $matcher;

    public function setUp()
    {
        $this->matcher = new BuiltinMatcher('startsWith', ['Ray']);
    }

    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = $this->matcher->matchesClass($class, ['Ray\Aop']);
        $this->assertTrue($isMatched);
    }

    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = $this->matcher->matchesMethod($method, ['get']);
        $this->assertTrue($isMatched);
    }

    public function testInvalidBuiltinMatcher()
    {
        $this->expectException(InvalidMatcherException::class);
        new BuiltinMatcher('invalid', []);
    }
}
