<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Supports matching classes and methods
 */
interface Matchable
{
    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target);
}
