<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

class ReflectionClass extends \ReflectionClass implements Reader
{
    /**
     * @var WeavedInterface
     */
    private $object;

    /**
     * Dependencies
     *
     * @param WeavedInterface $object
     */
    public function setObject(WeavedInterface $object)
    {
        $this->object = $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getAnnotations()
    {
        return unserialize($this->object->classAnnotations);
    }

    /**
     * {@inheritDoc}
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
