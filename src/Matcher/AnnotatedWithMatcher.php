<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\AbstractMatcher;

final class AnnotatedWithMatcher extends AbstractMatcher
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        parent::__construct();
        $this->reader = new AnnotationReader();
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        /** @var array<string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getClassAnnotation($class, $annotation);

        return $annotation ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        /** @var array<string> $arguments */
        [$annotation] = $arguments;
        $annotation = $this->reader->getMethodAnnotation($method, $annotation);

        return $annotation ? true : false;
    }
}
