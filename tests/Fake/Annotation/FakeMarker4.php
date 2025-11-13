<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class FakeMarker4
{
    public function __construct(private array $a, private int $b)
    {
    }
}
