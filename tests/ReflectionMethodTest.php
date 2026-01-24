<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class ReflectionMethodTest extends TestCase
{
    public function testGetAnnotationMatchesChildClass(): void
    {
        $method = new ReflectionMethod(FakeClassWithChildAttribute::class, 'annotatedMethod');
        $annotation = $method->getAnnotation(FakeParentAttribute::class);
        $this->assertInstanceOf(FakeChildAttribute::class, $annotation);
    }
}
