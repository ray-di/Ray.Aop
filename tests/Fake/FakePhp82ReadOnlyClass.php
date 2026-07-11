<?php

declare(strict_types=1);

namespace Ray\Aop;

readonly class FakePhp82ReadOnlyClass
{
    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
