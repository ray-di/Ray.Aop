<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\MatchInterface;

class IsLogicalOr implements MatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        list($matcherA, $matcherB) = $args;
        // a or b
        $isOr = ($matcherA($name, $target) or $matcherB($name, $target));
        if ($args <= 4) {
            return $isOr;
        }
        $isOr = $this->moreArgsOr($args, $isOr, $name, $target);

        return $isOr;
    }

    /**
     * @param array  $args
     * @param bool   $isOr
     * @param string $name
     * @param string $target
     *
     * @return bool
     */
    public function moreArgsOr(array $args, $isOr, $name, $target)
    {
        foreach ($args as $arg) {
            $isOr = ($isOr or $arg($name, $target));
        }

        return $isOr;
    }
}
