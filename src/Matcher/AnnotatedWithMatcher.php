<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\AbstractMatcher;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionClass;
use ReflectionMethod;

final class AnnotatedWithMatcher extends AbstractMatcher
{
    /** @var Reader */
    private $reader;

    public function __construct()
    {
        $this->reader = ServiceLocator::getReader();

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getClassAnnotation($class, $annotation);

        return (bool) $annotation;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getMethodAnnotation($method, $annotation);

        return (bool) $annotation;
    }
}
