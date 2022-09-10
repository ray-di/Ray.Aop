<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Ray\Aop\Annotation\FakeMarker3;
use Traversable;

class FakeTypedMock
{
    /** @FakeMarker3  */
    #[FakeMarker3]
    public function passIterator(ArrayIterator $iterator): Traversable
    {
        return $iterator;
    }
}
