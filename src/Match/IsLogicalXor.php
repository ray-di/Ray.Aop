<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\MatchInterface;

class IsLogicalXor implements MatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        list($matcherA, $matcherB) = $args;
        $isXor = ($matcherA($name, $target) xor $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isXor;
        }
        $args = array_slice($args, 4);
        foreach ($args as $arg) {
            $isXor = ($isXor xor $arg($name, $target));
        }

        return $isXor;
    }
}
