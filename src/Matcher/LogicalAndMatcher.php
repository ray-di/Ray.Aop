<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

final class LogicalAndMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isAnd = $isAnd && $matcher->matchesClass($class, $matcher->getArguments());
        }

        return $isAnd;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        $isAnd = true;
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isAnd = $isAnd && $matcher->matchesMethod($method, $matcher->getArguments());
        }

        return $isAnd;
    }
}
