<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\MatchInterface;

class IsLogicalNot implements MatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        list($matcher) = $args;
        $isNot = !($matcher($name, $target));

        return $isNot;
    }
}
