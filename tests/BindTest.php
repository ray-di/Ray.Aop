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
        $this->assertSame($this->bind->bindings['getDouble'], $interceptors);
    }

    public function testInvoke()
    {
        $this->bind->bindInterceptors('getDouble', [new FakeDoubleInterceptor, new FakeDoubleInterceptor]);
        $bind = $this->bind;
        $interceptors = $bind('getDouble');
        $this->assertSame(2, count($interceptors));
        $this->assertInstanceOf(FakeDoubleInterceptor::class, $interceptors[0]);
        $this->assertInstanceOf(FakeDoubleInterceptor::class, $interceptors[1]);
    }

    public function testBind()
    {
        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut((new Matcher)->startsWith('Ray'), (new Matcher)->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertArrayHasKey('getDouble', $this->bind->bindings);
        $this->assertSame($this->bind->bindings['getDouble'], $interceptors);
    }

    public function testBindUnmatched()
    {
        $interceptors = [new FakeDoubleInterceptor];
        $pointcut = new Pointcut((new Matcher)->startsWith('XXX'), (new Matcher)->startsWith('get'), $interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertSame($this->bind->bindings, []);
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
}

