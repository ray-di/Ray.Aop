<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use ReflectionException;

use function serialize;
use function unserialize;

class AnnotatedMatcherTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testSerilize(): void
    {
        $this->assertInstanceOf(AnnotatedMatcher::class, unserialize(serialize(new AnnotatedMatcher('startsWith', ['a']))));
    }
}
