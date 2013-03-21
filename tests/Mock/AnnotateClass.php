<?php

namespace Ray\Aop\Tests\Mock;

use /** @noinspection PhpUnusedAliasInspection */
    Ray\Aop\Tests\Annotation\Resource;
use /** @noinspection PhpUnusedAliasInspection */
    Ray\Aop\Tests\Annotation\Marker;

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
