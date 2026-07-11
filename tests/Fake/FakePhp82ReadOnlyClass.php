<?php

declare(strict_types=1);

namespace Ray\Aop;

readonly class FakePhp82ReadOnlyClass
{
    public function foo(): string
    {
        return 'foo';
    }

    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
