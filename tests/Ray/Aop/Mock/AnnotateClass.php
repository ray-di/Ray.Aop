<?php

namespace Ray\Aop\Mock;

use /** @noinspection PhpUnusedAliasInspection */
    Ray\Aop\Annotation\Resource;
use /** @noinspection PhpUnusedAliasInspection */
    Ray\Aop\Annotation\Marker;

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
    public function getDouble($a)
    {
        return $a * 2;
    }
}
