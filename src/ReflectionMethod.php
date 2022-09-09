<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\ServiceLocator\ServiceLocator;

use function array_key_exists;
use function assert;
use function class_exists;
use function is_object;
use function is_string;
use function property_exists;
use function unserialize;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /** @var ?WeavedInterface */
    private $object;

    /** @var string */
    private $method = '';

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object, \ReflectionMethod $method): void
    {
        $this->object = $object;
        $this->method = $method->name;
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
        $class = new ReflectionClass($originalClass);
        $class->setObject($this->object);

        return $class;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations(): array
    {
        $object = $this->object;
        if (is_object($object) && property_exists($object, 'methodAnnotations') && is_string($object->methodAnnotations)) {
            return $this->getCachedAnnotations($object->methodAnnotations);
        }

        assert(class_exists($this->class));
        /** @var list<object> $annotations */
        $annotations = ServiceLocator::getReader()->getMethodAnnotations(new \ReflectionMethod($this->class, (string) $this->name));

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

    /**
     * @return list<object>
     */
    private function getCachedAnnotations(string $methodAnnotations): array
    {
        /** @var array<string, list<object>> $annotations */
        $annotations = unserialize($methodAnnotations, ['allowed_classes' => true]);
        if (array_key_exists($this->method, $annotations)) {
            return $annotations[$this->method];
        }

        return [];
    }
}
