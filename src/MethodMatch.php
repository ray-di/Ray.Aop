<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;

final class MethodMatch
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var BindInterface
     */
    private $bind;

    public function __construct(BindInterface $bind)
    {
        $this->bind = $bind;
        $this->reader = new AnnotationReader;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param Pointcut[]               $pointcuts
     */
    public function __invoke(\ReflectionClass $class, \ReflectionMethod $method, array $pointcuts) : void
    {
        /** @var array<int, object> $annotations */
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

    /**
     * @param \ReflectionClass<object> $class
     */
    private function annotatedMethodMatchBind(\ReflectionClass $class, \ReflectionMethod $method, Pointcut $pointCut) : void
    {
        $isMethodMatch = $pointCut->methodMatcher->matchesMethod($method, $pointCut->methodMatcher->getArguments());
        if (! $isMethodMatch) {
            return;
        }
        $isClassMatch = $pointCut->classMatcher->matchesClass($class, $pointCut->classMatcher->getArguments());
        if (! $isClassMatch) {
            return;
        }
        /** @var MethodInterceptor[] $interceptors */
        $interceptors = $pointCut->interceptors;
        $this->bind->bindInterceptors($method->name, $interceptors);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param Pointcut[]               $pointcuts
     * @param array<int, object>       $annotations
     *
     * @return Pointcut[]
     */
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
