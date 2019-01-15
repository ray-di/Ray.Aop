<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CompilerInterface
{
    /**
     * Compile class
     */
    public function compile(string $class, BindInterface $bind) : string;

    /**
     * Return new instance weaved interceptor(s)
     *
     * @return object
     */
    public function newInstance(string $class, array $args, BindInterface $bind);
}
