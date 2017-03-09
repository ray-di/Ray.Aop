<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

interface Reader
{
    /**
     * Gets the annotations applied to a method.
     *
     * @return array an array of Annotations
     */
    public function getAnnotations();

    /**
     * Gets a method annotation.
     *
     * @param string $annotationName the name of the annotation
     *
     * @return object|null the Annotation or NULL, if the requested annotation does not exist
     */
    public function getAnnotation($annotationName);
}
