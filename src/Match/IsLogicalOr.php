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
        $args = func_num_args();
        // a or b
        $isOr = ($matcherA($name, $target) or $matcherB($name, $target));
        if ($args <= 4) {
            return $isOr;
        }
        $isOr = $this->moreArgsOr(func_get_args(), $isOr, $name, $target);

        return $isOr;
    }

    /**
     * @param bool $isOr
     */
    public function moreArgsOr(array $args, $isOr, $name, $target)
    {
        // a or b or c ...
        $args = array_slice($args, 4);
        foreach ($args as $arg) {
            $isOr = ($isOr or $arg($name, $target));
        }

        return $isOr;
    }
}
