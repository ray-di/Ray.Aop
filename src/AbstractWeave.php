<?php

declare(strict_types=1);

namespace Ray\Aop;

abstract class AbstractWeave implements WeavedInterface
{
    /**
     * @var string
     */
    public $methodAnnotations;

    /**
     * @var string
     */
    public $classAnnotations;
}
