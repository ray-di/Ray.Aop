<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CodeGenInterface
{
    public function generate(string $class, \ReflectionClass $sourceClass, BindInterface $bind) : string;
}
