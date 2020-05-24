<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CompilerInterface
{
    /**
     * Compile class
     *
     * @param class-string $class
     */
    public function compile(string $class, BindInterface $bind) : string;

    /**
     * Return new instance weaved interceptor(s)
     *
     * @param class-string      $class
     * @param array<int, mixed> $args
     *
     * @return object
     */
    public function newInstance(string $class, array $args, BindInterface $bind);
}
