<?php

declare(strict_types=1);

namespace Ray\Aop;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class FakeChildAttr extends FakeParentAttr
{
}
