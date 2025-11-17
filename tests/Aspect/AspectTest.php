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
    /** @var Aspect */
    private $aspect;

    protected function setUp(): void
    {
        $this->aspect = new Aspect();
    }

    public function testTmpDir(): void
    {
        $this->assertInstanceOf(Aspect::class, new Aspect(dirname(__DIR__) . '/tmp'));
    }

    public function testTmpDirNotWritable(): void
    {
        $this->expectException(NotWritableException::class);
        new Aspect('/__INVALID_DIR__');
    }

    public function testNewInstance(): void
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

    public function testNewInstanceWithNoBound(): void
    {
        $insntance = $this->aspect->newInstance(FakeNonFinalClass::class);
        $this->assertInstanceOf(FakeNonFinalClass::class, $insntance);
    }

    public function testAnnotateMatcher(): void
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
}
