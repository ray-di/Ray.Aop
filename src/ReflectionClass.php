<?php

declare(strict_types=1);
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
    public function setObject(WeavedInterface $object) : void
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotations() : array
    {
        return unserialize($this->object->classAnnotations);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotation(string $annotationName)
    {
        $annotations = $this->getAnnotations();
        if (array_key_exists($annotationName, $annotations)) {
            return $annotations[$annotationName];
        }

        return null;
    }
}
