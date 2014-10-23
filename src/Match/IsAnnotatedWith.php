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
use Ray\Aop\MatchInterface;

final class IsAnnotatedWith implements MatchInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader;
    }

    /**
     * Return annotated match
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param string $name
     * @param bool   $target
     * @param array  $args
     *
     * @return bool
     */
    public function __invoke($name, $target, array $args)
    {
        list($annotationName) = $args;
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
