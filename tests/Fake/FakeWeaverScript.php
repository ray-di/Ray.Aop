<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeWeaverScript
{
    public function returnSame($a)
    {
        return $a;
    }
}
