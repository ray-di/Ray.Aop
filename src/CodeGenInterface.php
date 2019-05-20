<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CodeGenInterface
{
    public function generate(\ReflectionClass $sourceClass, BindInterface $bind) : Code;
}
