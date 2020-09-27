<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeMockNoDoc
{
    public function returnSame($a)
    {
        return $a;
    }
}
