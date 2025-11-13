<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class FakeMarker
{
    public function __construct(public int $value)
    {
    }
}
