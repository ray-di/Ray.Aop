<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class PointcutTest extends TestCase
{
    public function testNew() : void
    {
        $pointCunt = new Pointcut(
            new BuiltinMatcher('startsWith', ['Ray']),
            new BuiltinMatcher('startsWith', ['get']),
            [new FakeInterceptor()]
        );
        $this->assertInstanceOf(Pointcut::class, $pointCunt);
    }
}
