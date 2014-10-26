<?php

namespace Ray\Aop;

class BuiltinMatcherTest extends \PHPUnit_Framework_TestCase
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
}
