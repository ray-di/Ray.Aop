<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\Matchable;

class IsLogicalNot
{
    public function __invoke($name, $target, Matchable $matcher)
    {
        $isNot = !($matcher($name, $target));

        return $isNot;
    }
}
