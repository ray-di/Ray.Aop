<?php

namespace Ray\Aop;

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
