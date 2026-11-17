<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;
use Ray\Aop\FakePhp81Enum;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class FakeMarkerText
{
    /** @param array<array-key, mixed> $list */
    public function __construct(
        public string $title,
        public array $list,
        public FakePhp81Enum $fruit,
    ) {
    }
}
