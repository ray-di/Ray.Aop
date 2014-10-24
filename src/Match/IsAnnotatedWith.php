<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\AbstractMatcher;
use Doctrine\Common\Annotations\AnnotationReader;

class IsAnnotatedWith extends AbstractMatcher
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($annotation) = $arguments;
        $annotation = $this->reader->getClassAnnotation($class, $annotation);

        return $annotation ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        list($annotation) = $arguments;
        $annotation = $this->reader->getMethodAnnotation($method, $annotation);

        return $annotation ? true : false;
    }
}
