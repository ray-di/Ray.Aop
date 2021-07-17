<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

use function serialize;
use function unserialize;

class AnnotatedMatcherTest extends TestCase
{
    public function testSerialize(): void
    {
        $object = new AnnotatedMatcher('startsWith', ['a']);
        $this->assertInstanceOf(AnnotatedMatcher::class, unserialize(serialize($object)));
    }
}
