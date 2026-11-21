<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeDefaultTextClass
{
    /** @param array<array-key, mixed> $list */
    public function get(
        string $text = "a\nb",
        array $list = ['a key' => "a\nvalue", 'nested' => ['b value', "c\tvalue\n"]],
    ): string {
        return $text;
    }
}
