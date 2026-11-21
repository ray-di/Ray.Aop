<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class FakeClassRefMarker
{
    /** @param class-string $class */
    public function __construct(public string $class, public string $name = '')
    {
    }
}
