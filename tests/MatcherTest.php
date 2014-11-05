<?php

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;
use Ray\Aop\Exception\InvalidArgument;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnBuildInMatcher()
    {
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->any());
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalAnd(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalOr(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalNot(new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->startsWith('a'));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->subclassesOf(FakeClass::class));
    }

    public function testValidationForAnnotatedWith()
    {
        $this->setExpectedException(InvalidAnnotation::class);
        (new Matcher)->annotatedWith('__invalid_class');
    }

    public function testValidationForStartsWith()
    {
        $this->setExpectedException(InvalidArgument::class);
        (new Matcher)->startsWith(0);
    }

    public function testValidationForSubclassesOf()
    {
        $this->setExpectedException(InvalidArgument::class);
        (new Matcher)->subclassesOf('__invalid_class');
    }
}
