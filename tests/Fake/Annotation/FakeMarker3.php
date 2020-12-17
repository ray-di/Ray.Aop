<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarker3
{
}
