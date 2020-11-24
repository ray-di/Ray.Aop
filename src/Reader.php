<?php

declare(strict_types=1);

namespace Ray\Aop;

interface Reader
{
    /**
     * Gets the annotations applied to a method.
     *
     * @return list<object> an array of Annotations
     */
    public function getAnnotations(): array;

    /**
     * Gets a method annotation.
     *
     * @param string $annotationName the name of the annotation
     *
     * @return object|null the Annotation or NULL, if the requested annotation does not exist
     */
    public function getAnnotation(string $annotationName);
}
