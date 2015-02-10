<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

interface CompilerInterface
{
    /**
     * @param string        $class
     * @param BindInterface $bind
     *
     * @return string
     */
    public function compile($class, BindInterface $bind);

    /**
     * Return new instance weaved interceptor(s)
     *
     * @param string        $class
     * @param array         $args
     * @param BindInterface $bind
     *
     * @return object
     */
    public function newInstance($class, array $args, BindInterface $bind);
}
