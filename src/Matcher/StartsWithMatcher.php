<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Override;
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Types;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function is_string;
use function str_starts_with;

/** @psalm-import-type Arguments from Types */
final class StartsWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var Arguments $arguments */
        [$startsWith] = $arguments;
        assert(is_string($startsWith));

        return str_starts_with($class->name, $startsWith);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var Arguments $arguments */
        [$startsWith] = $arguments;
        assert(is_string($startsWith));

        return str_starts_with($method->name, $startsWith);
    }
}
