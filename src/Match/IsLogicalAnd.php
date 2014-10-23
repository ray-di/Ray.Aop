<?php
/**
 * This file is part of the {package} package
 *
 * @package {package}
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Ray\Aop\Match;

use Ray\Aop\MatchInterface;

class IsLogicalAnd implements MatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        list($matcherA, $matcherB) = $args;
        $isAnd = ($matcherA($name, $target) and $matcherB($name, $target));
        if ($args <= 4) {
            return $isAnd;
        }
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
