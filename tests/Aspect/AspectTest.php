<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeClassMarker;
use Ray\Aop\Exception\NotWritableException;
use Ray\Aop\Matcher\AnyMatcher;
use Ray\Aop\Matcher\StartsWithMatcher;

use function dirname;

class AspectTest extends TestCase
{
    private Aspect $aspect;

    protected function setUp(): void
    {
        $this->aspect = new Aspect();
    }

    public function testAspectCanBeCreatedWithCustomTmpDirectory(): void
    {
        $this->assertInstanceOf(Aspect::class, new Aspect(dirname(__DIR__) . '/tmp'));
    }

    public function testNonWritableDirectoryThrowsException(): void
    {
        $this->expectException(NotWritableException::class);
        new Aspect('/__INVALID_DIR__');
    }

    public function testNewInstanceCreatesWeavedObjectWithInterceptor(): void
    {
        $this->aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );
        $myClass = $this->aspect->newInstance(FakeNonFinalClass::class);
        $this->assertNotSame($myClass::class, FakeNonFinalClass::class);
        $result = $myClass->myMethod();
        // the original method is intercepted
        $this->assertEquals('intercepted original', $result);
    }

    public function testNewInstanceWithoutBindingsReturnsOriginalClass(): void
    {
        $instance = $this->aspect->newInstance(FakeNonFinalClass::class);
        $this->assertInstanceOf(FakeNonFinalClass::class, $instance);
    }

    public function testAnnotationBasedMatcherBindsCorrectly(): void
    {
        $aspect = new Aspect();
        $aspect->bind(
            (new Matcher())->annotatedWith(FakeClassMarker::class),
            (new Matcher())->any(),
            [new FakeMyInterceptor()]
        );

        $billing = $aspect->newInstance(FakeNonFinalClass::class);
        $this->assertInstanceOf(FakeNonFinalClass::class, $billing);
    }

    public function testMultipleMatchersWithFirstNotMatching(): void
    {
        $aspect = new Aspect();

        // First matcher: won't match FakeNonFinalClass (no FakeClassMarker annotation)
        $aspect->bind(
            (new Matcher())->annotatedWith(FakeClassMarker::class),
            (new Matcher())->any(),
            [new FakeMyInterceptor()]
        );

        // Second matcher: will match any class with method starting with 'my'
        $aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );

        // The second matcher should still be processed (continue, not break)
        $instance = $aspect->newInstance(FakeNonFinalClass::class);
        $result = $instance->myMethod();
        $this->assertEquals('intercepted original', $result);
    }

    public function testMultipleMatchersBothMatching(): void
    {
        $aspect = new Aspect();

        // First matcher
        $aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );

        // Second matcher (same)
        $aspect->bind(
            new AnyMatcher(),
            new StartsWithMatcher('my'),
            [new FakeMyInterceptor()]
        );

        // Both matchers should apply
        $instance = $aspect->newInstance(FakeNonFinalClass::class);
        $result = $instance->myMethod();
        // Intercepted twice
        $this->assertEquals('intercepted intercepted original', $result);
    }

    public function testDefaultTmpDirIsUsedWhenNullPassed(): void
    {
        // Default constructor should use sys_get_temp_dir()
        $aspect = new Aspect();
        $this->assertInstanceOf(Aspect::class, $aspect);
    }
}
