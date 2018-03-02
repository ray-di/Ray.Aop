<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
abstract class AbstractAssisted
{
    /**
     * Add null default to listed parameters
     *
     * @var array
     */
    public $values;
}
