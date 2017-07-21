<?php

/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Php71;

interface CompilerInterface
{
    /**
     * @param string        $class
     * @param BindInterface $bind
     *
     * @return string
     */
    public function compile(string $class, BindInterface $bind) : string;

    /**
     * Return new instance weaved interceptor(s)
     *
     * @param string        $class
     * @param array         $args
     * @param BindInterface $bind
     *
     * @return object
     */
    public function newInstance(string $class, array $args, BindInterface $bind);
}
