<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package {package}
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

class LogicalAndMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            /** @var $matcher AbstractMatcher */
            $isAnd = $isAnd && $matcher->matchesClass($class, []);
        }

        return $isAnd;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            /** @var $matcher AbstractMatcher */
            $isAnd = $isAnd && $matcher->matchesMethod($method, []);
        }

        return $isAnd;
    }
}
