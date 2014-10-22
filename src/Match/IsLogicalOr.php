<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\Matchable;

class IsLogicalOr
{
    public function __invoke($name, $target, Matchable $matcherA, Matchable $matcherB)
    {
        // a or b
        $isOr = ($matcherA($name, $target) or $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isOr;
        }
        // a or b or c ...
        $args = array_slice(func_get_args(), 4);
        foreach ($args as $arg) {
            $isOr = ($isOr or $arg($name, $target));
        }

        return $isOr;
    }
}
