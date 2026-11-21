<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarkerText;

class FakeAttributeTextClass
{
    #[FakeMarkerText(
        "Next page\nsecond line\tafter tab",
        list: ['a key' => 'a  value', 'nested' => ['b value', "c\tvalue\n"]],
        fruit: FakePhp81Enum::Orange,
    )]
    public function get(): string
    {
        return 'get';
    }
}
