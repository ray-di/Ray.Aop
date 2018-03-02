<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Matcher;

use PHPUnit\Framework\TestCase;
use Ray\Aop\FakeClass;

class SubclassesOfMatcherTest extends TestCase
{
    public function testMatchesClass()
    {
        $class = new \ReflectionClass(FakeClass::class);
        $isMatched = (new SubclassesOfMatcher)->matchesClass($class, [FakeClass::class]);

        $this->assertTrue($isMatched);
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidAnnotationException
     */
    public function testMatchesMethod()
    {
        $method = new \ReflectionMethod(FakeClass::class, 'getDouble');
        $isMatched = (new SubclassesOfMatcher)->matchesMethod($method, ['get']);

        $this->assertTrue($isMatched);
    }
}
