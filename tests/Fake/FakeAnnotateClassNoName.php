<?php
namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker2;
use Ray\Aop\Annotation\FakeMarker3;

/**
 * @Ray\Aop\FakeResource
 */
class FakeAnnotateClassNoName
{
    public $a = 0;

    /**
     * @FakeMarker3
     * @FakeMarker2
     * @FakeMarker
     */
    public function getDouble($a)
    {
        return $a * 2;
    }

    public function returnSame($a)
    {
    }
}
