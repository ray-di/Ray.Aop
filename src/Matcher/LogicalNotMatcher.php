<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function assert;

final class LogicalNotMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        [$matcher] = $arguments;
        assert($matcher instanceof AbstractMatcher);

        return ! $matcher->matchesClass($class, $matcher->getArguments());
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        [$matcher] = $arguments;
        assert($matcher instanceof AbstractMatcher);

        return ! $matcher->matchesMethod($method, $matcher->getArguments());
    }
}
