<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;
use ReflectionException;

class MatcherTest extends TestCase
{
    /** @throws ReflectionException */
    public function testAllMatcherMethodsReturnBuiltinMatcher(): void
    {
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->any());
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->annotatedWith(FakeResource::class));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalAnd(new FakeMatcher(), new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalAnd(new FakeMatcher(), new FakeMatcher(), new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalOr(new FakeMatcher(), new FakeMatcher(false)));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalOr(new FakeMatcher(), new FakeMatcher(), new FakeMatcher(false)));

        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->logicalNot(new FakeMatcher()));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->startsWith('a'));
        $this->assertInstanceOf(BuiltinMatcher::class, (new Matcher())->subclassesOf(FakeClass::class));
    }

    /** @throws ReflectionException */
    public function testAnnotatedWithThrowsExceptionForInvalidClass(): void
    {
        $this->expectException(InvalidAnnotationException::class);

        (new Matcher())->annotatedWith('__invalid_class');
    }

    /** @throws ReflectionException */
    public function testSubclassesOfThrowsExceptionForInvalidClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Matcher())->subclassesOf('__invalid_class');
    }

    /** @throws ReflectionException */
    public function testSubclassesOfPassesArgumentsCorrectly(): void
    {
        $matcher = (new Matcher())->subclassesOf(FakeClass::class);
        $this->assertSame([FakeClass::class], $matcher->getArguments());
    }

    /** @throws ReflectionException */
    public function testLogicalNotPassesArgumentsCorrectly(): void
    {
        $innerMatcher = new FakeMatcher();
        $matcher = (new Matcher())->logicalNot($innerMatcher);
        $this->assertSame([$innerMatcher], $matcher->getArguments());
    }
}
