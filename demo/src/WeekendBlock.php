<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class WeekendBlock
{
}
