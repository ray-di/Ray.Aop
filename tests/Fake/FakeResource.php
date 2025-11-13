<?php

declare(strict_types=1);

namespace Ray\Aop;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class FakeResource
{
}
