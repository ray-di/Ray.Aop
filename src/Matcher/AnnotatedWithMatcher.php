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
        /** @psalm-suppress MixedAssignment $annotation */
        /** @psalm-suppress ArgumentTypeCoercion */
        /** @phpstan-ignore-next-line argument.type, argument.templateType */
        $annotation = $class->getAnnotation($annotationName);

        return (bool) $annotation;
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

        /** @psalm-suppress ArgumentTypeCoercion */
        /** @phpstan-ignore-next-line argument.type, argument.templateType */
        $annotation = $method->getAnnotation($annotationName);

        return (bool) $annotation;
    }
}
