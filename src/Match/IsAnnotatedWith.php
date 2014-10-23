<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\AbstractMatcher;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Matched;

final class IsAnnotatedWith
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader = null)
    {
        $this->reader = $reader ?: new AnnotationReader;
    }

    /**
     * Return is annotated with
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param mixed  $name           string(class name) | ReflectionMethod
     * @param bool   $target         AbstractMatcher::TARGET_CLASS | AbstractMatcher::TARGET_METHOD
     * @param string $annotationName annotation name
     *
     * @return bool | Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function __invoke($name, $target, $annotationName)
    {
        if ($name instanceof \ReflectionMethod) {
            return $this->isAnnotatedMethod($name, $annotationName);
        }
        if ($target !== AbstractMatcher::TARGET_CLASS) {
            return $this->setAnnotations($name, $annotationName);
        }
        $annotation = $this->reader->getClassAnnotation(new \ReflectionClass($name), $annotationName);
        $hasAnnotation = $annotation ? true : false;

        return $hasAnnotation;
    }

    /**
     * @param \ReflectionMethod $method
     * @param string            $annotationName
     *
     * @return array|bool
     */
    private function isAnnotatedMethod(\ReflectionMethod $method, $annotationName)
    {
        $annotation = $this->reader->getMethodAnnotation($method, $annotationName);
        if (! $annotation) {
            return false;
        }
        $matched = new Matched;
        $matched->methodName = $method->name;
        $matched->annotation = $annotation;

        return [$matched];
    }

    /**
     * Set annotations
     *
     * @param string $class
     * @param string $annotationName
     *
     * @return array
     */
    private function setAnnotations($class, $annotationName)
    {
        $methods = (new \ReflectionClass($class))->getMethods();
        $result = [];
        foreach ($methods as $method) {
            $annotation = $this->reader->getMethodAnnotation($method, $annotationName);
            /** @var $annotation null | Annotation */
            if ($annotation) {
                $matched = new Matched;
                $matched->methodName = $method->name;
                $matched->annotation = $annotation;
                $result[] = $matched;
            }
        }

        return $result;
    }
}
