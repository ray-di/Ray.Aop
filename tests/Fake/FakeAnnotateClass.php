<?php
namespace Ray\Aop;

/**
 * @Ray\Aop\FakeResource
 * @FakeClassAnnotation("item")
 */
class FakeAnnotateClass
{
    public $a = 0;

    /**
     * @Ray\Aop\FakeMarker3
     * @FakeMarker2
     * @FakeMarker(1)
     * @FakeMarker(2)
     */
    public function getDouble($a)
    {
        return $a * 2;
    }
}
