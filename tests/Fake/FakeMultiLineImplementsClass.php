<?php

declare(strict_types=1);

namespace Ray\Aop;

// PSR-12 allows the implements list to be split over several lines.
class FakeMultiLineImplementsClass implements
    FakeNullInterface,
    FakeNullInterface1
{
    public function returnSame(int $a): int
    {
        return $a;
    }
}
