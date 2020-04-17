<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

final class LogicalOrMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
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
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
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
