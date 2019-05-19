<?php
namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker2;
use Ray\Aop\Annotation\FakeMarker3;

/**
 * @FakeResource
 * @FakeClassAnnotation
 */
class FakeAssistedConsumer
{
    /**
     * @FakeAssisted({"b", "c"})
     * @FakeMarker
     * @FakeMarker2
     * @FakeMarker3
     */
    public function run($a, $b, $c)
    {
        return [$a, $b, $c];
    }
}
