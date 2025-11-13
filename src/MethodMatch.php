<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionClass;
use ReflectionMethod;

use function array_key_exists;

/**
 * @psalm-import-type MethodInterceptors from Types
 * @psalm-import-type MethodBindings from Types
 * @psalm-import-type ClassBindings from Types
 * @psalm-import-type MatcherConfig from Types
 * @psalm-import-type Arguments from Types
 * @psalm-import-type Pointcuts from Types
 */
final readonly class MethodMatch
{
    public function __construct(private BindInterface $bind)
    {
    }

    /**
     * @param ReflectionClass<object> $class
     * @param Pointcuts               $pointcuts
     */
    public function __invoke(ReflectionClass $class, \Ray\Aop\ReflectionMethod $method, array $pointcuts): void
    {
        /** @var list<object> $annotations */
        $annotations = $method->getAnnotations();
        // priority bind
        foreach ($pointcuts as $key => $pointcut) {
            if (! ($pointcut instanceof PriorityPointcut)) {
                continue;
            }

            $this->annotatedMethodMatchBind($class, $method, $pointcut);
            unset($pointcuts[$key]);
        }

        $onion = $this->onionOrderMatch($class, $method, $pointcuts, $annotations);

        // default binding
        foreach ($onion as $pointcut) {
            $this->annotatedMethodMatchBind($class, $method, $pointcut);
        }
    }

    /** @param ReflectionClass<object> $class */
    private function annotatedMethodMatchBind(ReflectionClass $class, ReflectionMethod $method, Pointcut $pointCut): void
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
     * @param ReflectionClass<object> $class
     * @param Pointcuts               $pointcuts
     * @param list<object>            $annotations
     *
     * @return Pointcuts
     */
    private function onionOrderMatch(
        ReflectionClass $class,
        ReflectionMethod $method,
        array $pointcuts,
        array $annotations,
    ): array {
        // method bind in annotation order
        foreach ($annotations as $annotation) {
            $annotationIndex = $annotation::class;
            if (! array_key_exists($annotationIndex, $pointcuts)) {
                continue;
            }

            $this->annotatedMethodMatchBind($class, $method, $pointcuts[$annotationIndex]);
            unset($pointcuts[$annotationIndex]);
        }

        return $pointcuts;
    }
}
