<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeCtorArgsClass
{
    public function __construct(
        public string $name,
        public int $n = 0,
    ) {
    }

    public function greet(): string
    {
        return $this->name . $this->n;
    }
}
