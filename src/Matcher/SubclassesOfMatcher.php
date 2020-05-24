<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Exception\InvalidAnnotationException;

final class SubclassesOfMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        /** @var array<class-string> $arguments */
        [$superClass] = $arguments;

        return $class->isSubclassOf($superClass) || ($class->name === $superClass);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        unset($method, $arguments);

        throw new InvalidAnnotationException('subclassesOf is only for class match');
    }
}
