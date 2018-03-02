<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

abstract class AbstractWeave implements WeavedInterface
{
    public $methodAnnotations;
    public $classAnnotations;
}
