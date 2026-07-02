<?php

declare(strict_types=1);

namespace Ray\Aop\Benchmark;

class BenchService
{
    public function doWork(int $a, int $b): int
    {
        return $a + $b;
    }

    public function doString(string $s): string
    {
        return \strtoupper($s);
    }

    public function noIntercept(): int
    {
        return 42;
    }
}
