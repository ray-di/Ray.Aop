<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\AbstractMatcher;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionClass;
use ReflectionMethod;

final class AnnotatedWithMatcher extends AbstractMatcher
{
    /** @var Reader */
    private $reader;

    /**
     * @throws AnnotationException
     */
    public function __construct()
    {
        parent::__construct();
        $this->reader = ServiceLocator::getReader();
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getClassAnnotation($class, $annotation); // @phpstan-ignore-line

        return (bool) $annotation;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        /** @var array<class-string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getMethodAnnotation($method, $annotation); // @phpstan-ignore-line

        return (bool) $annotation;
    }
}
