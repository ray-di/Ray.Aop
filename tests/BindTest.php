<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker2;
use Ray\Aop\Annotation\FakeMarker3;

class BindTest extends TestCase
{
    protected Bind $bind;

    /** @var array<MethodInterceptor> */
    protected array $interceptors;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bind = new Bind();
    }

    public function testBindInterceptorsToMethod(): void
    {
        $interceptors = [new FakeDoubleInterceptor(), new FakeDoubleInterceptor()];
        $this->bind->bindInterceptors('getDouble', $interceptors);
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBindWithMatchingPointcut(): void
    {
        $interceptors = [new FakeDoubleInterceptor()];
        $pointcut = new Pointcut((new Matcher())->startsWith('Ray'), (new Matcher())->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->getBindings());
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBindToClassWithConstructor(): void
    {
        $interceptors = [new FakeDoubleInterceptor()];
        $pointcut = new Pointcut((new Matcher())->startsWith('Ray'), (new Matcher())->startsWith('get'), $interceptors);
        $this->bind->bind(FakeConstructorClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->getBindings());
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBindWithNonMatchingPointcutCreatesNoBindings(): void
    {
        $interceptors = [new FakeDoubleInterceptor()];
        $pointcut = new Pointcut((new Matcher())->startsWith('XXX'), (new Matcher())->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertSame($this->bind->getBindings(), []);
    }

    #[DoesNotPerformAssertions]
    public function testToStringConversion(): void
    {
        $nullBind = (string) (new Bind());

        $interceptors = [new FakeDoubleInterceptor()];
        $pointcut = new Pointcut((new Matcher())->startsWith('Ray'), (new Matcher())->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $bindString = (string) $this->bind;
    }

    public function testBindWithCustomMatcher(): void
    {
        $interceptors = [new FakeDoubleInterceptor()];
        $pointcut = new Pointcut(new FakeMatcher(), (new Matcher())->any(), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->getBindings());
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBindWithNonMatchingClassMatcher(): void
    {
        $pointcut = new Pointcut(new FakeMatcher(false), (new Matcher())->any(), [new FakeDoubleInterceptor()]);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayNotHasKey('getDouble', $this->bind->getBindings());
    }

    public function testMultipleAnnotationBasedInterceptorsAreOrderedCorrectly(): void
    {
        $onion1 = new FakeOnionInterceptor1();
        $onion2 = new FakeOnionInterceptor2();
        $onion3 = new FakeOnionInterceptor3();
        $pointcut0 = new Pointcut((new Matcher())->any(), (new Matcher())->startsWith('XXX'), [$onion1]);
        $pointcut1 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker::class), [$onion1]);
        $pointcut2 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker2::class), [$onion2]);
        $pointcut3 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker3::class), [$onion3]);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut0, $pointcut1, $pointcut2, $pointcut3]);
        $actual = $this->bind->getBindings();
        $expect = [
            'getDouble' => [$onion3, $onion2, $onion1],
        ];
        $this->assertSame($expect, $actual);
    }

    public function testPriorityPointcutIsExecutedFirst(): void
    {
        $onion1 = new FakeOnionInterceptor1();
        $onion2 = new FakeOnionInterceptor2();
        $onion3 = new FakeOnionInterceptor3();
        $onion4 = new FakeOnionInterceptor4();
        $pointcut0 = new Pointcut((new Matcher())->any(), (new Matcher())->startsWith('XXX'), [$onion1]);
        $pointcut1 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker::class), [$onion1]);
        $pointcut2 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker2::class), [$onion2]);
        $pointcut3 = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeMarker3::class), [$onion3]);
        $pointcut4 = new PriorityPointcut((new Matcher())->annotatedWith(FakeResource::class), (new Matcher())->any(), [$onion4]);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut0, $pointcut1, $pointcut2, $pointcut3, $pointcut4]);
        $actual = $this->bind->getBindings();
        $expect = [
            'getDouble' => [$onion4, $onion3, $onion2, $onion1],
        ];
        $this->assertSame($expect, $actual);
    }

    public function testNonAnnotationPointcutDoesNotInstantiateMethodAttributes(): void
    {
        FakeCountingAttribute::$instances = 0;

        $pointcut = new Pointcut((new Matcher())->any(), (new Matcher())->any(), [new FakeDoubleInterceptor()]);
        $this->bind->bind(FakeCountingAttributeClass::class, [$pointcut]);

        $this->assertArrayHasKey('run', $this->bind->getBindings());
        $this->assertSame(0, FakeCountingAttribute::$instances);
    }

    public function testAnnotatedPointcutDoesNotInstantiateMethodAttributes(): void
    {
        FakeCountingAttribute::$instances = 0;

        $pointcut = new Pointcut((new Matcher())->any(), (new Matcher())->annotatedWith(FakeCountingAttribute::class), [new FakeDoubleInterceptor()]);
        $this->bind->bind(FakeCountingAttributeClass::class, [$pointcut]);

        $this->assertArrayHasKey('run', $this->bind->getBindings());
        $this->assertSame(0, FakeCountingAttribute::$instances);
    }
}
