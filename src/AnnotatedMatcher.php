<?php

declare(strict_types=1);

namespace Ray\Aop;

use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Matcher for annotations
 *
 * @psalm-import-type MatcherName from Types
 */
final class AnnotatedMatcher extends BuiltinMatcher
{
    /** @var class-string */
    public readonly string $annotation;

    /**
     * @param MatcherName            $matcherName
     * @param array{0: class-string} $arguments   Single element array containing annotation class name
     */
    public function __construct(string $matcherName, array $arguments)
    {
        parent::__construct($matcherName, $arguments);

        $this->annotation = $arguments[0];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var class-string $annotationName */
        $annotationName = $arguments[0];

        return $class->getAttributes($annotationName, ReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var class-string $annotationName */
        $annotationName = $arguments[0];

        return $method->getAttributes($annotationName, ReflectionAttribute::IS_INSTANCEOF) !== [];
    }
}
