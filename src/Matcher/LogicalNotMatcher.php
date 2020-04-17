<?php

declare(strict_types=1);

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
        assert($matcher instanceof AbstractMatcher);

        return ! $matcher->matchesClass($class, $matcher->getArguments());
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($matcher) = $arguments;
        assert($matcher instanceof AbstractMatcher);

        return ! $matcher->matchesMethod($method, [$arguments]);
    }
}
