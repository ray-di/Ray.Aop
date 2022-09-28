<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\ServiceLocator\ServiceLocator;

use function assert;
use function class_exists;
use function is_object;
use function is_string;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /** @var ?WeavedInterface */
    private $object;

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object): void
    {
        $this->object = $object;
    }

    public function getDeclaringClass(): ReflectionClass
    {
        if (! is_object($this->object)) {
            assert(class_exists($this->class));

            return new ReflectionClass($this->class);
        }

        $parencClass = (new \ReflectionClass($this->object))->getParentClass();
        assert($parencClass instanceof \ReflectionClass);
        $originalClass = $parencClass->name;
        assert(class_exists($originalClass));

        return new ReflectionClass($originalClass);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations(): array
    {
        assert(class_exists($this->class));
        assert(is_string($this->name));
        /** @var list<object> $annotations */
        $annotations = ServiceLocator::getReader()->getMethodAnnotations(new \ReflectionMethod($this->class, $this->name));

        return $annotations;
    }

    /**
     * @param class-string<T> $annotationName
     *
     * @return T|null
     *
     * @template T of object
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getAnnotation(string $annotationName)
    {
        $annotations = $this->getAnnotations();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }
}
