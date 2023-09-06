<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use ReflectionClass;
use ReflectionMethod;

use function strpos;

final class StartsWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var array<string> $arguments */
        [$startsWith] = $arguments;

        return strpos($class->name, $startsWith) === 0;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var array<string> $arguments */
        [$startsWith] = $arguments;

        return strpos($method->name, $startsWith) === 0;
    }
}
