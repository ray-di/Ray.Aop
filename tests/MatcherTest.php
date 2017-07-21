<?php
namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;

class MatcherTest extends TestCase
{
    public function testReturnBuildInMatcher()
    {
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->any());
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalAnd(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalAnd(new FakeMatcher, new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalOr(new FakeMatcher, new FakeMatcher(false)));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalOr(new FakeMatcher, new FakeMatcher, new FakeMatcher(false)));

        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->logicalNot(new FakeMatcher));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->startsWith('a'));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher)->subclassesOf(FakeClass::class));
    }

    public function testValidationForAnnotatedWith()
    {
        $this->setExpectedException(InvalidAnnotationException::class);
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
