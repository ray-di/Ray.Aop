<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Override;
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Types;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function is_string;

/** @psalm-import-type Arguments from Types */
final class SubclassesOfMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var Arguments $arguments */
        [$superClass] = $arguments;
        assert(is_string($superClass));

        /** @psalm-suppress ArgumentTypeCoercion */
        return $class->isSubclassOf($superClass) || ($class->name === $superClass);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        unset($method, $arguments);

        throw new InvalidAnnotationException('subclassesOf is only for class match');
    }
}
