<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function assert;

final class LogicalOrMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isMatch = $matcher->matchesClass($class, $matcher->getArguments());
            if ($isMatch === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        foreach ($arguments as $matcher) {
            assert($matcher instanceof AbstractMatcher);
            $isMatch = $matcher->matchesMethod($method, $matcher->getArguments());
            if ($isMatch === true) {
                return true;
            }
        }

        return false;
    }
}
