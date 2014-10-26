<?php

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;
use Ray\Aop\Exception\InvalidArgumentException;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnBuildInMatcher()
    {
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->any());
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalAnd(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalOr(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalNot(new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->startsWith('a'));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->subclassesOf(FakeClass::class));
    }

    public function testValidationForAnnotatedWith()
    {
        $this->setExpectedException(InvalidAnnotation::class);
        (new Matcher)->annotatedWith('__invalid_class');
    }

    public function testValidationForStartsWith()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        (new Matcher)->startsWith(0);
    }

    public function testValidationForSubclassesOf()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        (new Matcher)->subclassesOf('__invalid_class');
    }
}
