<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @extends \ReflectionClass<object>
 */
class ReflectionClass extends \ReflectionClass implements Reader
{
    /**
     * @var null|WeavedInterface
     */
    private $object;

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object) : void
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations() : array
    {
        $object = $this->object;
        if (isset($object->classAnnotations)) {
            return unserialize($object->classAnnotations, ['allowed_classes' => true]);
        }

        return (new AnnotationReader)->getClassAnnotations(new \ReflectionClass($this->name));
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
