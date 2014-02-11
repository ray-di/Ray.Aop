<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Interface for compiler
 */
interface CompilerInterface
{
    /**
     * Compile
     *
     * @param string $class
     * @param Bind   $bind
     *
     * @return string
     */
    public function compile($class, Bind $bind);

    /**
     * Return new aspect weaved object instance
     *
     * @param string $class
     * @param array  $args
     * @param Bind   $bind
     *
     * @return object
     */
    public function newInstance($class, array $args, Bind $bind);

    /**
     * Return aop class directory
     *
     * @return string
     */
    public function getAopClassDir();
}
