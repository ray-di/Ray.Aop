<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

class LogicalNotMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($matcher) = $arguments;
        /** @var $matcher AbstractMatcher */
        $isNot = ! $matcher->matchesClass($class, [$arguments]);

        return $isNot;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        list($matcher) = $arguments;
        /** @var $matcher AbstractMatcher */
        $isNot = ! $matcher->matchesMethod($method, [$arguments]);

        return $isNot;
    }
}
