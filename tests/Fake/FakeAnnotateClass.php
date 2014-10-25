<?php

namespace Ray\Aop;

/**
 * @Ray\Aop\FakeResource
 */
class FakeAnnotateClass
{
    public $a = 0;

    /**
     * @Ray\Aop\FakeMarker
     */
    public function getDouble($a)
    {
        return $a * 2;
    }
}
