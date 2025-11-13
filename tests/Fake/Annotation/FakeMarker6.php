<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;
use Ray\Aop\FakePhp81Enum;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class FakeMarker6
{
    public function __construct(
        public FakePhp81Enum $fruit1,
        public FakePhp81Enum $fruit2,
    ) {
    }
}
