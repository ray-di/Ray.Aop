<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\FakeAnnotateClass;
use Ray\Aop\FakeMarker;
use Ray\Aop\FakeResource;

class AnnotatedWithMatcherTest extends TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeAnnotateClass::class);
        $isMatched = (new AnnotatedWithMatcher)->matchesClass($class, [FakeResource::class]);

        $this->assertTrue($isMatched);
    }

    public function testMatchMethod()
    {
        $method = new \ReflectionMethod(FakeAnnotateClass::class, 'getDouble');
        $isMatched = (new AnnotatedWithMatcher)->matchesMethod($method, [FakeMarker::class]);

        $this->assertTrue($isMatched);
    }
}
