<?php

namespace Ray\Aop;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnBuildInMatcher()
    {
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->any());
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalAnd(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalOr(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalXOr(new FakeMatcher, new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->logicalNot(new FakeMatcher));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->startsWith('a'));
        $this->assertInstanceOf(BuiltInMatcher::class, (new Matcher)->subclassesOf(FakeClass::class));
    }
}
