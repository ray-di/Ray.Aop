<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarker
{
    /** @var int */
    public $value;

    public function __construct($value)
    {
        $this->value['value'] ?? $value;
    }
}
