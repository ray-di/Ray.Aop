<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class FakeClassMarker
{
}
