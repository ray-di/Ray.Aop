<?php

namespace Ray\Aop\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("ALL")
 */
#[Attribute(Attribute::TARGET_ALL)]
class FakeAllPublicMethods extends AllPublicMethods
{
}