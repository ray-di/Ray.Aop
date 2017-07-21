<?php

/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Php71;

interface CodeGenInterface
{
    /**
     * Generate PHP Code
     */
    public function generate(string $class, \ReflectionClass $sourceClass, BindInterface $bind) : string;
}
