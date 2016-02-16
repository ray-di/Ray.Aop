<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

final class ReflectionMethod extends \ReflectionMethod
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
     * Dependencies
     *
     * @param WeavedInterface   $object
     * @param \ReflectionMethod $method
     */
    public function setObject(WeavedInterface $object, \ReflectionMethod $method)
    {
        $this->object = $object;
        $this->method = $method->getName();
    }

    /**
     * Gets the annotations applied to a method.
     *
     * @return array An array of Annotations.
     */
    public function getAnnotations()
    {
        $annotations = unserialize($this->object->methodAnnotations);
        if (isset($annotations[$this->method])) {
            return $annotations[$this->method];
        }

        return [];
    }

    /**
     * Gets a method annotation.
     *
     * @param string $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getAnnotation($annotationName)
    {
        $annotations = $this->getAnnotations();
        if (isset($annotations[$annotationName])) {
            return $annotations[$annotationName];
        }

        return null;
    }
}
