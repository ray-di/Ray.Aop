<?php

declare(strict_types=1);

namespace Ray\Aop;


use Ray\Aop\Annotation\FakeAllPublicMethods;

/**
 * @FakeResource
 * @FakeAllPublicMethods
 */
#[FakeAllPublicMethods]
class FakeAnnotateClassAllPublicMethods
{
    public int $a = 0;

    public function getDouble($a)
    {
        return $a * 2;
    }

    public function getSome($a): float|int
    {
        return $a + 2;
    }
}
