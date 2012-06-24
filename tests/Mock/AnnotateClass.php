<?php

namespace Ray\Aop\Tests\Mock;

use Ray\Aop\Tests\Annotation\Resource;
use Ray\Aop\Tests\Annotation\Marker;

/**
 * Test class for Ray.Aop
 *
 * @Resource
 */
class AnnotateClass
{
    public $a = 0;

    /**
     * @Marker
     */
    public function getDobule($a)
    {
        return $a * 2;
    }
}
