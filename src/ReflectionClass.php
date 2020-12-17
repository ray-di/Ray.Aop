<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\ServiceLocator\ServiceLocator;

use function is_string;
use function unserialize;

/**
 * @extends \ReflectionClass<object>
 */
class ReflectionClass extends \ReflectionClass implements Reader
{
    /** @var WeavedInterface|null */
    private $object;

    /**
     * Set dependencies
     */
    public function setObject(WeavedInterface $object): void
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations(): array
    {
        $object = $this->object;
        if (isset($object->classAnnotations) && is_string($object->classAnnotations)) {
            /** @var list<object> $annotations */
            $annotations = unserialize($object->classAnnotations, ['allowed_classes' => true]);

            return $annotations;
        }

        /** @var list<object> $annotations */
        $annotations = ServiceLocator::getReader()->getClassAnnotations(new \ReflectionClass($this->name));

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
