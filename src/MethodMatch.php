<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionClass;
use ReflectionMethod;

use function is_a;
use function is_string;

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
        // priority bind
        foreach ($pointcuts as $key => $pointcut) {
            if (! ($pointcut instanceof PriorityPointcut)) {
                continue;
            }

            $this->annotatedMethodMatchBind($class, $method, $pointcut);
            unset($pointcuts[$key]);
        }

        $onion = $this->onionOrderMatch($class, $method, $pointcuts);

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
     *
     * @return Pointcuts
     */
    private function onionOrderMatch(
        ReflectionClass $class,
        ReflectionMethod $method,
        array $pointcuts,
    ): array {
        if (! $this->hasAnnotationPointcut($pointcuts)) {
            return $pointcuts;
        }

        // method bind in annotation order
        foreach ($method->getAttributes() as $attribute) {
            /** @var class-string $annotationIndex */
            $annotationIndex = $attribute->getName();
            foreach ($pointcuts as $key => $pointcut) {
                if (! $pointcut->methodMatcher instanceof AnnotatedMatcher) {
                    continue;
                }

                if (! is_string($key)) {
                    continue;
                }

                /** @var class-string $key */
                if ($annotationIndex !== $key && ! is_a($annotationIndex, $key, true)) {
                    continue;
                }

                $this->annotatedMethodMatchBind($class, $method, $pointcut);
                unset($pointcuts[$key]);
            }
        }

        return $pointcuts;
    }

    /** @param Pointcuts $pointcuts */
    private function hasAnnotationPointcut(array $pointcuts): bool
    {
        foreach ($pointcuts as $pointcut) {
            if ($pointcut->methodMatcher instanceof AnnotatedMatcher) {
                return true;
            }
        }

        return false;
    }
}
