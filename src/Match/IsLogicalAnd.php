<?php
/**
 * This file is part of the {package} package
 *
 * @package {package}
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Ray\Aop\Match;

use Ray\Aop\Matchable;

class IsLogicalAnd
{
    public function __invoke($name, $target, Matchable $matcherA, Matchable $matcherB)
    {
        $isAnd = ($matcherA($name, $target) and $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isAnd;
        }
        $args = array_slice(func_get_args(), 4);
        $bool = $this->moreArgs($args, $isAnd, $name, $target);

        return $bool;
    }

    /**
     * @param array  $args
     * @param bool   $isAnd
     * @param string $name
     * @param bool   $target
     *
     * @return bool
     */
    private function moreArgs(array $args, $isAnd, $name, $target)
    {
        foreach ($args as $arg) {
            $isAnd = ($isAnd and $arg($name, $target));
        }

        return $isAnd;
    }
}
