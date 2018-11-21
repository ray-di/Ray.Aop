<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CodeGenInterface
{
    /**
     * @param string $class
     *
     * @return string
     */
    public function generate($class, \ReflectionClass $sourceClass, BindInterface $bind);
}
