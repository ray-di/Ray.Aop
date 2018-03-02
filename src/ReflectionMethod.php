<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

final class ReflectionMethod extends \ReflectionMethod implements Reader
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
     * Set dependencies
     */
    public function setObject(WeavedInterface $object, \ReflectionMethod $method)
    {
        $this->object = $object;
        $this->method = $method->name;
    }

    /**
     * @return ReflectionClass
     */
    public function getDeclaringClass() : ReflectionClass
    {
        $originalClass = (new \ReflectionClass($this->object))->getParentClass()->name;
        $class = new ReflectionClass($originalClass);
        $class->setObject($this->object);

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotations() : array
    {
        /** @var AbstractWeave $object */
        $object = $this->object;
        $annotations = unserialize($object->methodAnnotations);
        if (array_key_exists($this->method, $annotations)) {
            return $annotations[$this->method];
        }

        return [];
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
