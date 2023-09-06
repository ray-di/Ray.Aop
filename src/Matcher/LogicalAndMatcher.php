<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function assert;

final class LogicalAndMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isAnd = $isAnd && $matcher->matchesClass($class, $matcher->getArguments());
        }

        return $isAnd;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isAnd = $isAnd && $matcher->matchesMethod($method, $matcher->getArguments());
        }

        return $isAnd;
    }
}
