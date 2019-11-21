<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class WeaverTest extends TestCase
{
    public function test__construct()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor]);
        $bind = (new Bind)->bind(FakeMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $this->assertInstanceOf(Weaver::class, $weaver);

        return $weaver;
    }

    /**
     * @depends test__construct
     */
    public function testNewInstance(Weaver $weaver)
    {
        $weaved = $weaver->newInstance(FakeMock::class, []);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * @depends test__construct
     */
    public function testCachedWeaver(Weaver $weaver)
    {
        $weaver = unserialize(serialize($weaver));
        $weaved = $weaver->newInstance(FakeMock::class, []);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }
}
