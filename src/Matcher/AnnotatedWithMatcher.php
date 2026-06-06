<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Override;
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Types;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function is_string;

/** @psalm-import-type Arguments from Types */
final class AnnotatedWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        assert($class instanceof \Ray\Aop\ReflectionClass);
        /** @var Arguments $arguments */
        [$annotationName] = $arguments;
        assert(is_string($annotationName));
        /** @var class-string $annotationName */

        return $class->getAttributes($annotationName, ReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        assert($method instanceof \Ray\Aop\ReflectionMethod);
        /** @var Arguments $arguments */
        [$annotationName] = $arguments;
        assert(is_string($annotationName));
        /** @var class-string $annotationName */

        return $method->getAttributes($annotationName, ReflectionAttribute::IS_INSTANCEOF) !== [];
    }
}
