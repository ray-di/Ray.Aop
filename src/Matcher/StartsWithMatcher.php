<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

final class StartsWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        /** @var array<string> $arguments */
        [$startsWith] = $arguments;

        return strpos($class->name, $startsWith) === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        /** @var array<string> $arguments */
        [$startsWith] = $arguments;

        return strpos($method->name, $startsWith) === 0;
    }
}
