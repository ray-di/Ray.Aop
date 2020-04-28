<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class WeaverTest extends TestCase
{
    public function test__construct() : Weaver
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor]);
        $bind = (new Bind)->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $this->assertInstanceOf(Weaver::class, $weaver);

        return $weaver;
    }

    /**
     * @depends test__construct
     */
    public function testWeave(Weaver $weaver) : void
    {
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(\class_exists($className, false));
    }

    /**
     * This tests cover compiled aop file loading.
     *
     * @covers \Ray\Aop\Weaver::loadClass
     * @covers \Ray\Aop\Weaver::weave
     */
    public function testWeaveLoad() : void
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), []);
        $bind = (new Bind)->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp_unerase');
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(\class_exists($className, false));
    }

    /**
     * @depends test__construct
     */
    public function testNewInstance(Weaver $weaver) : void
    {
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        assert($weaved instanceof FakeWeaverMock);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * @depends test__construct
     */
    public function testCachedWeaver(Weaver $weaver) : void
    {
        $weaver = unserialize(serialize($weaver));
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        assert($weaved instanceof FakeWeaverMock);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }
}
