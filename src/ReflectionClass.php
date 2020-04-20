<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

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
     */
    public function getAnnotations() : array
    {
        $object = $this->object;
        if (isset($object->annotations)) {
            return unserialize($object->classAnnotations); // @phpstan-ignore-line
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
