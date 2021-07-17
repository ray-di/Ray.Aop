<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionClass;
use ReflectionMethod;

final class AnnotatedWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = ServiceLocator::getReader()->getClassAnnotation($class, $annotation);

        return (bool) $annotation;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = ServiceLocator::getReader()->getMethodAnnotation($method, $annotation);

        return (bool) $annotation;
    }
}
