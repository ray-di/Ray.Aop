<?php

declare(strict_types=1);

namespace Ray\Aop;

interface CompilerInterface
{
    /**
     * @param string $class
     *
     * @return string
     */
    public function compile($class, BindInterface $bind);

    /**
     * Return new instance weaved interceptor(s)
     *
     * @param string $class
     *
     * @return object
     */
    public function newInstance($class, array $args, BindInterface $bind);
}
