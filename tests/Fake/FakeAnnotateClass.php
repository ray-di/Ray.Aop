<?php
namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker2;
use Ray\Aop\Annotation\FakeMarker3;

/**
 * @FakeResource
 * @FakeClassAnnotation("item")
 */
class FakeAnnotateClass
{
    public $a = 0;

    /**
     * @FakeMarker3
     * @FakeMarker2
     * @FakeMarker(1)
     * @FakeMarker(2)
     */
    public function getDouble($a)
    {
        return $a * 2;
    }
}
