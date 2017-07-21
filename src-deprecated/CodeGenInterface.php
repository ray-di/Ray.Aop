<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

interface CodeGenInterface
{
    /**
     * @param string           $class
     * @param \ReflectionClass $sourceClass
     * @param BindInterface    $bind
     *
     * @return string
     */
    public function generate($class, \ReflectionClass $sourceClass, BindInterface $bind);
}
