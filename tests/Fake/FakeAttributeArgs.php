<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Annotation\FakeClassRefMarker;
use Ray\Aop\Annotation\FakeMarker4;
use Ray\Aop\Annotation\FakeMarkerName;

#[FakeClassRefMarker(FakeNullInterface::class, name: 'x')]
class FakeAttributeArgs
{
    #[FakeMarkerName(1, 'admin user', true)]
    #[FakeMarker4(['read write', 'k y' => "a\n b"], 1)]
    public function run(string $sep = "a\n b"): string
    {
        return $sep;
    }
}
