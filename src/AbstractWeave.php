<?php

declare(strict_types=1);

namespace Ray\Aop;

abstract class AbstractWeave implements WeavedInterface
{
    public $methodAnnotations;
    public $classAnnotations;
}
