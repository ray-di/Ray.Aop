<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarkerName
{
    public function __construct(public int $a, public string $b, public bool $c)
    {
    }
}
