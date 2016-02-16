<?php

namespace Ray\Aop;

use Ray\Aop\FakeAssisted;

class FakeAssistedConsumer
{
    /**
     * @FakeAssisted({"b", "c"})
     */
    public function run($a, $b, $c)
    {
        return [$a, $b, $c];
    }
}
