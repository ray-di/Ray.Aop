<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /**
     * @var ?WeavedInterface
     */
    private $object;

    /**
     * @var string
     */
    private $method = '';

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object, \ReflectionMethod $method) : void
    {
        $this->object = $object;
        $this->method = $method->name;
    }

    public function getDeclaringClass() : ReflectionClass
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
    public function getAnnotations() : array
    {
        $object = $this->object;
        if (! isset($object->methodAnnotations) || ! is_string($object->methodAnnotations)) {
            /** @var array<int, object> $annotations */
            $annotations = (new AnnotationReader)->getMethodAnnotations(new \ReflectionMethod($this->class, $this->name));

            return $annotations;
        }
        /** @var array<string, array<int, object>> $annotations */
        $annotations = unserialize($object->methodAnnotations, ['allowed_classes' => true]);
        if (array_key_exists($this->method, $annotations)) {
            return $annotations[$this->method];
        }

        return [];
    }

    /**
     * {@inheritdoc}
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
