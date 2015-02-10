<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use Doctrine\Common\Annotations\Reader;

final class Bind implements BindInterface
{
    /**
     * @var array
     */
    private $bindings = [];

    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader;
    }

    /**
     * @param string $class
     * @param array  $pointcuts
     *
     * @return $this
     */
    public function bind($class, array $pointcuts)
    {
        $pointcuts = $this->getAnnnotationPointcuts($pointcuts);
        $this->annotatedMethodsMatch(new \ReflectionClass($class), $pointcuts);

        return $this;
    }

    /**
     * @param ReflectionClass $class
     * @param array           $pointcuts
     */
    private function annotatedMethodsMatch(\ReflectionClass $class, array &$pointcuts)
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $this->annotatedMethodMatch($class, $method, $pointcuts);
        }
    }

    /**
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     * @param Pointcut[]       $pointcuts
     */
    private function annotatedMethodMatch(\ReflectionClass $class, \ReflectionMethod $method, array &$pointcuts)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        // priority bind
        foreach ($pointcuts as $key => $pointcut) {
            if ($pointcut instanceof PriorityPointcut) {
                $this->annotatedMethodMatchBind($class, $method, $pointcut);
                unset($pointcuts[$key]);
            }
        }
        $pointcuts = $this->onionOrderMatch($class, $method, $pointcuts, $annotations);

        // default binding
        foreach ($pointcuts as $pointcut) {
            $this->annotatedMethodMatchBind($class, $method, $pointcut);
        }
    }

    /**
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     * @param PointCut         $pointCut
     */
    private function annotatedMethodMatchBind(\ReflectionClass $class, \ReflectionMethod $method, PointCut $pointCut)
    {
        $isMethodMatch = $pointCut->methodMatcher->matchesMethod($method, $pointCut->methodMatcher->getArguments());
        if (! $isMethodMatch) {
            return;
        }
        $isClassMatch = $pointCut->classMatcher->matchesClass($class, $pointCut->classMatcher->getArguments());
        if (! $isClassMatch) {
            return;
        }
        $this->bindInterceptors($method->name, $pointCut->interceptors);
    }


    /**
     * {@inheritdoc}
     */
    public function bindInterceptors($method, array $interceptors)
    {
        $this->bindings[$method] = !isset($this->bindings[$method]) ? $interceptors : array_merge(
            $this->bindings[$method],
            $interceptors
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $shortHash = function ($data) {
            return strtr(rtrim(base64_encode(pack('H*', sprintf('%u', crc32(serialize($data))))), '='), '+/', '-_');
        };

        return $shortHash(serialize($this->bindings));
    }

    /**
     * @param Pointcut[] $pointcuts
     */
    public function getAnnnotationPointcuts(array &$pointcuts)
    {
        $keyPointcuts = [];
        foreach ($pointcuts as $key => $pointcut) {
            if ($pointcut->methodMatcher instanceof AnnotatedMatcher) {
                $key = $pointcut->methodMatcher->annotation;
            }
            $keyPointcuts[$key] = $pointcut;
        }

        return $keyPointcuts;
    }

    /**
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     * @param array            $pointcuts
     * @param array            $annotations
     *
     * @return array
     */
    private function onionOrderMatch(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        array &$pointcuts,
        $annotations
    ) {
        // method bind in annotation order
        foreach ($annotations as $annotation) {
            $annotationIndex = get_class($annotation);
            if (isset($pointcuts[$annotationIndex])) {
                $this->annotatedMethodMatchBind($class, $method, $pointcuts[$annotationIndex]);
                unset($pointcuts[$annotationIndex]);
            }
        }

        return $pointcuts;
    }
}
