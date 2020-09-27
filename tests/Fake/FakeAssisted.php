<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.Aop package.
 */

namespace Ray\Aop;

use Ray\Aop\Annotation\AbstractAssisted;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class FakeAssisted extends AbstractAssisted
{
    /** @var array<string> */
    public $values;
}
