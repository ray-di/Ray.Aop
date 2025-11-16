<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * @deprecated Use native PHP 8 attributes with ReflectionClass/ReflectionMethod::getAttributes() instead
 */
interface Reader
{
    /**
     * Gets the annotations applied to a method.
     *
     * @return list<object> an array of Annotations
     *
     * @deprecated Use ReflectionMethod::getAttributes() instead
     */
    public function getAnnotations(): array;

    /**
     * Gets a method annotation.
     *
     * @param string $annotationName the name of the annotation
     *
     * @return object|null the Annotation or NULL, if the requested annotation does not exist
     *
     * @deprecated Use ReflectionMethod::getAttributes() instead
     */
    public function getAnnotation(string $annotationName);
}