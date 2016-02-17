<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

interface Reader
{
    /**
     * Gets the annotations applied to a method.
     *
     * @return array An array of Annotations.
     */
    public function getAnnotations();

    /**
     * Gets a method annotation.
     *
     * @param string $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getAnnotation($annotationName);
}
