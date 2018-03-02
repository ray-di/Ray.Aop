<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class PointcutTest extends TestCase
{
    public function testNew()
    {
        $pointCunt = new Pointcut(
            new BuiltinMatcher('startsWith', ['Ray']),
            new BuiltinMatcher('startsWith', ['get']),
            [new FakeInterceptor()]
        );
        $this->assertInstanceOf(Pointcut::class, $pointCunt);
    }
}
