<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

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

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        $this->reader = new AnnotationReader();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function bind($class, array $pointcuts) : BindInterface
    {
        $pointcuts = $this->getAnnotationPointcuts($pointcuts);
        $this->annotatedMethodsMatch(new \ReflectionClass($class), $pointcuts);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function bindInterceptors($method, array $interceptors) : BindInterface
    {
        $this->bindings[$method] = ! array_key_exists($method, $this->bindings) ? $interceptors : array_merge(
            $this->bindings[$method],
            $interceptors
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings() : array
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($salt) : string
    {
        unset($salt);

        return strtr(rtrim(base64_encode(pack('H*', sprintf('%u', crc32(serialize($this->bindings))))), '='), '+/', '-_');
    }

    /**
     * @param Pointcut[] $pointcuts
     *
     * @return Pointcut[]
     */
    public function getAnnotationPointcuts(array &$pointcuts) : array
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

    private function annotatedMethodsMatch(\ReflectionClass $class, array &$pointcuts)
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $this->annotatedMethodMatch($class, $method, $pointcuts);
        }
    }

    private function annotatedMethodMatch(\ReflectionClass $class, \ReflectionMethod $method, array $pointcuts)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        // priority bind
        foreach ($pointcuts as $key => $pointcut) {
            if ($pointcut instanceof PriorityPointcut) {
                $this->annotatedMethodMatchBind($class, $method, $pointcut);
                unset($pointcuts[$key]);
            }
        }
        $onion = $this->onionOrderMatch($class, $method, $pointcuts, $annotations);

        // default binding
        foreach ($onion as $pointcut) {
            $this->annotatedMethodMatchBind($class, $method, $pointcut);
        }
    }

    private function annotatedMethodMatchBind(\ReflectionClass $class, \ReflectionMethod $method, Pointcut $pointCut)
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

    private function onionOrderMatch(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        array $pointcuts,
        array $annotations
    ) : array {
        // method bind in annotation order
        foreach ($annotations as $annotation) {
            $annotationIndex = get_class($annotation);
            if (array_key_exists($annotationIndex, $pointcuts)) {
                $this->annotatedMethodMatchBind($class, $method, $pointcuts[$annotationIndex]);
                unset($pointcuts[$annotationIndex]);
            }
        }

        return $pointcuts;
    }
}
