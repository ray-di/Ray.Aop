<?php
namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

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

    /**
     * @expectedException \Ray\Aop\Exception\InvalidAnnotationException
     */
    public function testValidationForAnnotatedWith()
    {
        (new Matcher)->annotatedWith('__invalid_class');
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidArgumentException
     */
    public function testValidationForSubclassesOf()
    {
        (new Matcher)->subclassesOf('__invalid_class');
    }
}
