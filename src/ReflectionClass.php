<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\ServiceLocator\ServiceLocator;

/**
 * @extends \ReflectionClass<object>
 */
class ReflectionClass extends \ReflectionClass implements Reader
{
    /**
     * {@inheritdoc}
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getAnnotations(): array
    {
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
