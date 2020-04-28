<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class VariadicParamsTest extends TestCase
{
    public function testVariadicParams() : void
    {
        $compiler = new Compiler(__DIR__ . '/tmp');
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('variadicParam'), [new FakeDoubleInterceptor]);
        $bind = (new Bind)->bind(FakePhp71NullableClass::class, [$pointcut]);
        $fake = $compiler->newInstance(FakePhp71NullableClass::class, [], $bind);
        assert($fake instanceof FakePhp71NullableClass);
        $actual = $fake->variadicParam(1, 2);
        $this->assertSame(2, $actual);
    }
}
