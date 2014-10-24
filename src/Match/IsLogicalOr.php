<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\AbstractMatcher;

class IsLogicalOr extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        $isOr = false;
        foreach ($arguments as $matcher) {
            /** @var $matcher AbstractMatcher */
            $isOr = $isOr || $matcher->matchesClass($class, []);
        }

        return $isOr;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        $isOr = false;
        foreach ($arguments as $matcher) {
            /** @var $matcher AbstractMatcher */
            $isOr = $isOr || $matcher->matchesMethod($method, []);
        }

        return $isOr;
    }
}
