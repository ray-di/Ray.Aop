<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("ALL")
 */
#[Attribute(Attribute::TARGET_ALL)]
class AllPublicMethods
{
    private bool $allMethods = true;

    final public function isAllMethods(): bool
    {
        return $this->allMethods;
    }
}
