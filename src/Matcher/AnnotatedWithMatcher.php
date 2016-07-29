<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop\Matcher;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\AbstractMatcher;

class AnnotatedWithMatcher extends AbstractMatcher
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct()
    {
        parent::__construct();
        $this->reader = new AnnotationReader();
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
