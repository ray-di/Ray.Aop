<?php

declare(strict_types=1);

namespace Ray\Aop;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
abstract class FakeParentAttribute
{
}
