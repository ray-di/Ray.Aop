<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

use function class_exists;
use function passthru;
use function serialize;
use function unserialize;

class WeaverTest extends TestCase
{
    public function testConstructorCreatesWeaverInstance(): Weaver
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $this->assertInstanceOf(Weaver::class, $weaver);

        return $weaver;
    }

    #[Depends('testConstructorCreatesWeaverInstance')]
    public function testWeaveCreatesProxyClass(Weaver $weaver): void
    {
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    public function testWeaveLoadsCompiledAopFile(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), []);
        $bind = (new Bind())->bind(FakeWeaverMock::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp_unerase');
        $className = $weaver->weave(FakeWeaverMock::class);
        $this->assertTrue(class_exists($className, false));
    }

    #[Depends('testConstructorCreatesWeaverInstance')]
    public function testNewInstanceCreatesWeavedObject(Weaver $weaver): void
    {
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    #[Depends('testConstructorCreatesWeaverInstance')]
    public function testSerializedWeaverMaintainsFunctionality(Weaver $weaver): void
    {
        $weaver = unserialize(serialize($weaver));
        $this->assertInstanceOf(Weaver::class, $weaver);
        $weaved = $weaver->newInstance(FakeWeaverMock::class, []);
        $this->assertInstanceOf(FakeWeaverMock::class, $weaved);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testWeaveHandlesPrecompiledClass(): void
    {
        passthru('php ' . __DIR__ . '/script/weave.php');
        $pointcut = new Pointcut(
            (new Matcher())->any(),
            (new Matcher())->any(),
            [new FakeInterceptor()]
        );
        $bind = new Bind();
        $bind->bind(FakeWeaverScript::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');
        $className = $weaver->weave(FakeWeaverScript::class);
        $this->assertTrue(class_exists($className, false));
    }
}
