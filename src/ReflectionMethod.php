<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /**
     * @var null|WeavedInterface
     */
    private $object;

    /**
     * @var string
     */
    private $method;

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object, \ReflectionMethod $method) : void
    {
        $this->object = $object;
        $this->method = $method->name;
    }

    /**
     * @throws \ReflectionException
     */
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
     */
    public function getAnnotations() : array
    {
        $object = $this->object;
        if (! isset($object->annotations)) {
            return (new AnnotationReader)->getMethodAnnotations(new \ReflectionMethod($this->class, $this->name));
        }
        $annotations = unserialize($object->methodAnnotations); // @phpstan-ignore-line
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
