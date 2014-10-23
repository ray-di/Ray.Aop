<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

interface MatchInterface
{
    /**
     * @param string $name   class or method name
     * @param bool   $target class or method
     * @param array  $args   arguments for match
     *
     * @return bool
     */
    public function __invoke($name, $target, array $args);
}
