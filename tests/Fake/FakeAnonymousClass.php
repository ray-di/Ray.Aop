<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeAnonymousClass
{
    public function hasAnonymousClass(): object
    {
        return new class {};
    }
}
