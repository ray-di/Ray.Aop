<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class MatcherTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testReturnBuildInMatcher() : void
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
     * @throws \ReflectionException
     */
    public function testValidationForAnnotatedWith() : void
    {
        $this->expectException(\Ray\Aop\Exception\InvalidAnnotationException::class);

        (new Matcher)->annotatedWith('__invalid_class');
    }

    /**
     * @throws \ReflectionException
     */
    public function testValidationForSubclassesOf() : void
    {
        $this->expectException(\Ray\Aop\Exception\InvalidArgumentException::class);

        (new Matcher)->subclassesOf('__invalid_class');
    }
}
