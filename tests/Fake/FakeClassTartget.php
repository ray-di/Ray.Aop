<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker2;
use Ray\Aop\Annotation\FakeMarker3;

/**
 * @FakeResource
 * @FakeClassAnnotation("item")
 */
class FakeClassTartget
{
    /**
     * @FakeMarker(1)
     */
    public function __construct()
    {
    }

    /**
     * @FakeMarker(1)
     * @FakeMarker(2)
     */
    public function getDouble($a)
    {
        return $a * 2;
    }
}
