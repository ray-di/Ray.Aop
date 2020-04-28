<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CodeGenInterface
{
    /**
     * @param \ReflectionClass<object> $sourceClass
     */
    public function generate(\ReflectionClass $sourceClass, BindInterface $bind) : Code;
}
