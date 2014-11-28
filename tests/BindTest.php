<?php

namespace Ray\Aop;

class BindTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Bind
     */
    protected $bind;

    /**
     * @var array
     */
    protected $interceptors;

    protected function setUp()
    {
        parent::setUp();
        $this->bind = new Bind;
    }

    public function testBindInterceptors()
    {
        $interceptors = [new FakeDoubleInterceptor, new FakeDoubleInterceptor];
        $this->bind->bindInterceptors('getDouble', $interceptors);
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBind()
    {
        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut((new Matcher)->startsWith('Ray'), (new Matcher)->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->getBindings());
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testBindUnmatched()
    {
        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut((new Matcher)->startsWith('XXX'), (new Matcher)->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertSame($this->bind->getBindings(), []);
    }

    public function testToString()
    {
        $nullBind = (string)new Bind;
        $this->assertInternalType('string', $nullBind);

        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut((new Matcher)->startsWith('Ray'), (new Matcher)->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $bindString = (string)$this->bind;
        $this->assertInternalType('string', $bindString);
        $this->assertNotSame($nullBind, $bindString);
    }

    public function testMyMatcher()
    {
        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut(new FakeMatcher, (new Matcher)->any(), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->getBindings());
        $this->assertSame($this->bind->getBindings()['getDouble'], $interceptors);
    }

    public function testNotClassMatch()
    {
        $pointcut = new Pointcut(new FakeMatcher(false), (new Matcher)->any(), [new FakeDoubleInterceptor]);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayNotHasKey('getDouble', $this->bind->getBindings());
    }

    public function testOnionAnnotation()
    {
        $onion1 = new FakeOnionInterceptor1;
        $onion2 = new FakeOnionInterceptor2;
        $onion3 = new FakeOnionInterceptor3;
        $pointcut = new Pointcut((new Matcher)->any(), (new Matcher)->annotatedWith(FakeMarker::class), [$onion1]);
        $pointcut2 = new Pointcut((new Matcher)->any(), (new Matcher)->annotatedWith(FakeMarker2::class), [$onion2]);
        $pointcut3 = new Pointcut((new Matcher)->any(), (new Matcher)->annotatedWith(FakeMarker3::class), [$onion3]);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut, $pointcut2, $pointcut3]);
        $actual = $this->bind->getBindings();
        $expect = [
            'getDouble' => [$onion3, $onion2, $onion1]
        ];
        $this->assertSame($expect, $actual);
    }
}

