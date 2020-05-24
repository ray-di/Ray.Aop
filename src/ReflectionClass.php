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
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations() : array
    {
        $object = $this->object;
        if (isset($object->classAnnotations) && is_string($object->classAnnotations)) {
            /** @var array<int, object> $annotations */
            $annotations = unserialize($object->classAnnotations, ['allowed_classes' => true]);

            return $annotations;
        }
        /** @var array<int, object> $annotations */
        $annotations = (new AnnotationReader)->getClassAnnotations(new \ReflectionClass($this->name));

        return $annotations;
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
