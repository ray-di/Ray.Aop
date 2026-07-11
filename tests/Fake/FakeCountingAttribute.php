<?php

declare(strict_types=1);

namespace Ray\Aop;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class FakeCountingAttribute
{
    public static int $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }
}
