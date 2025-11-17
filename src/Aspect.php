<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\NotWritableException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function file_exists;
use function is_writable;
use function sys_get_temp_dir;

/**
 * Aspect class manages aspect weaving and method interception
 *
 * @psalm-import-type MethodInterceptors from Types
 * @psalm-import-type MethodBindings from Types
 * @psalm-import-type ClassBindings from Types
 * @psalm-import-type MatcherConfig from Types
 * @psalm-import-type MatcherConfigList from Types
 * @psalm-import-type Arguments from Types
 * @psalm-import-type MethodName from Types
 * @psalm-import-type ScriptDir from Types
 */
final class Aspect
{
    /**
     * Temporary directory for generated proxy classes
     *
     * @var ScriptDir
     */
    private readonly string $tmpDir;

    /**
     * Collection of matcher configurations
     *
     * @var MatcherConfigList
     */
    private array $matchers = [];

    /** @param ScriptDir|null $tmpDir Directory for generated proxy classes */
    public function __construct(string|null $tmpDir = null)
    {
        if ($tmpDir === null) {
            $tmp = sys_get_temp_dir();
            $tmpDir = $tmp !== '' ? $tmp : '/tmp';
        }

        if (! file_exists($tmpDir) || ! is_writable($tmpDir)) {
            throw new NotWritableException("{$tmpDir} is not writable.");
        }

        $this->tmpDir = $tmpDir;
    }

    /**
     * Bind interceptors to matched methods
     *
     * @param AbstractMatcher    $classMatcher  Class matcher
     * @param AbstractMatcher    $methodMatcher Method matcher
     * @param MethodInterceptors $interceptors  List of interceptors
     */
    public function bind(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors): void
    {
        $matcherConfig = [
            'classMatcher' => $classMatcher,
            'methodMatcher' => $methodMatcher,
            'interceptors' => $interceptors,
        ];

        $this->matchers[] = $matcherConfig;
    }

    /**
     * Create new instance with woven aspects
     *
     * @param class-string<T> $className Target class name
     * @param list<mixed>     $args      Constructor arguments
     *
     * @return T New instance with aspects
     *
     * @throws RuntimeException When temporary directory is not set for PHP-based AOP.
     *
     * @template T of object
     */
    public function newInstance(string $className, array $args = []): object
    {
        $bind = $this->createBind($className);
        $weaver = new Weaver($bind, $this->tmpDir);

        /** @psalm-var T */
        return $weaver->newInstance($className, $args);
    }

    /**
     * Create bind instance for the given class
     *
     * @param class-string $className
     */
    private function createBind(string $className): Bind
    {
        $bind = new Bind();
        $reflection = new ReflectionClass($className);

        foreach ($this->matchers as $matcher) {
            if (! $matcher['classMatcher']->matchesClass($reflection, $matcher['classMatcher']->getArguments())) {
                continue;
            }

            /** @var ReflectionMethod[] $methods */
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if (! $matcher['methodMatcher']->matchesMethod($method, $matcher['methodMatcher']->getArguments())) {
                    continue; // @codeCoverageIgnore
                }

                /** @var MethodName $methodName */
                $methodName = $method->getName();
                $bind->bindInterceptors($methodName, $matcher['interceptors']);
            }
        }

        return $bind;
    }
}
