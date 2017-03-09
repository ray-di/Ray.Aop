<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

class ReflectionClass extends \ReflectionClass implements Reader
{
    /**
     * @var WeavedInterface
     */
    private $object;

    /**
     * Set dependencies
     *
     * @param WeavedInterface $object
     */
    public function setObject(WeavedInterface $object)
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotations()
    {
        return unserialize($this->object->classAnnotations);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotation($annotationName)
    {
        $annotations = $this->getAnnotations();
        if (array_key_exists($annotationName, $annotations)) {
            return $annotations[$annotationName];
        }

        return null;
    }
}
