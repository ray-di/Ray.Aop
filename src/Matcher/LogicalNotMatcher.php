<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

final class LogicalNotMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($matcher) = $arguments;
        /* @var $matcher AbstractMatcher */
        $isNot = ! $matcher->matchesClass($class, $matcher->getArguments());

        return $isNot;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($matcher) = $arguments;
        /* @var $matcher AbstractMatcher */
        $isNot = ! $matcher->matchesMethod($method, [$arguments]);

        return $isNot;
    }
}
