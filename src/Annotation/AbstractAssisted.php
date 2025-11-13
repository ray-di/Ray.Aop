<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

abstract class AbstractAssisted
{
    /**
     * Add null default to listed parameters
     *
     * @var string[]
     * @readonly
     */
    public $values;
}
