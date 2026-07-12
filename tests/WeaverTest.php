<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

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

    public function testNewInstancePassesListConstructorArgs(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('greet'), [new NullInterceptor()]);
        $bind = (new Bind())->bind(FakeCtorArgsClass::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');

        $instance = $weaver->newInstance(FakeCtorArgsClass::class, ['alice', 7]);

        $this->assertInstanceOf(FakeCtorArgsClass::class, $instance);
        $this->assertInstanceOf(WeavedInterface::class, $instance);
        $this->assertSame('alice7', $instance->greet());
    }

    public function testNewInstancePassesNamedConstructorArgs(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('greet'), [new NullInterceptor()]);
        $bind = (new Bind())->bind(FakeCtorArgsClass::class, [$pointcut]);
        $weaver = new Weaver($bind, __DIR__ . '/tmp');

        // new $class(...$args) accepts named arguments when keys match parameter names.
        // Public type is list<mixed>; named bags are a runtime-supported extension.
        /** @phpstan-ignore argument.type (named ctor args via spread) */
        $instance = $weaver->newInstance(FakeCtorArgsClass::class, ['n' => 3, 'name' => 'bob']);

        $this->assertSame('bob3', $instance->greet());
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
        // Populate classCache, then ensure it is not restored after unserialize
        $weaver->weave(FakeWeaverMock::class);
        $serialized = serialize($weaver);
        $this->assertStringNotContainsString('classCache', $serialized);

        $weaver = unserialize($serialized);
        $this->assertInstanceOf(Weaver::class, $weaver);

        $cache = (new ReflectionProperty(Weaver::class, 'classCache'))->getValue($weaver);
        $this->assertSame([], $cache, 'classCache must not survive serialize/unserialize');

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

    public function testNewInstanceReturnsOriginalWhenNoBindings(): void
    {
        $bind = new Bind();
        $weaver = new Weaver($bind, __DIR__ . '/tmp');

        $instance = $weaver->newInstance(FakeWeaverMock::class, []);

        $this->assertInstanceOf(FakeWeaverMock::class, $instance);
        $this->assertNotInstanceOf(WeavedInterface::class, $instance);
    }
}
