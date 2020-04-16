<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

final class ReflectionMethod extends \ReflectionMethod implements Reader
{
    /**
     * @var WeavedInterface
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
        if (! $parencClass instanceof \ReflectionClass) {
            throw new \LogicException; // @codeCoverageIgnore
        }
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
        $isCompiled = $object instanceof WeavedInterface && isset($object->classAnnotations);
        if (! $isCompiled) {
            return (new AnnotationReader)->getMethodAnnotations(new \ReflectionMethod($this->class, $this->name));
        }
        $annotations = unserialize($object->methodAnnotations);
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
