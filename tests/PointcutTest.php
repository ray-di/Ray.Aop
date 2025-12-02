<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class PointcutTest extends TestCase
{
    public function testPointcutCanBeCreated(): void
    {
        $pointcut = new Pointcut(
            new BuiltinMatcher('startsWith', ['Ray']),
            new BuiltinMatcher('startsWith', ['get']),
            [new FakeInterceptor()]
        );
        $this->assertInstanceOf(Pointcut::class, $pointcut);
    }
}
