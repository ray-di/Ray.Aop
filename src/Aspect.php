<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\NotWritableException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function clearstatcache;
use function file_exists;
use function fileowner;
use function fileperms;
use function is_dir;
use function is_link;
use function is_writable;
use function mkdir;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;

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
            $tmpDir = self::privateTmpDir();
        }

        // Defensive validation for resolved temp dirs.
        // @codeCoverageIgnoreStart
        if ($tmpDir === '' || ! file_exists($tmpDir) || ! is_writable($tmpDir)) {
            throw new NotWritableException("{$tmpDir} is not writable.");
        }

        // @codeCoverageIgnoreEnd

        $this->tmpDir = $tmpDir;
    }

    /**
     * A directory under the system temp dir that only the current user can write
     *
     * Proxies are `require`d from the script dir, so one writable by another user lets that user run code here.
     * The check runs once: the sticky bit of the system temp dir keeps another user from replacing the directory afterwards.
     */
    private static function privateTmpDir(): string
    {
        $tmpDir = sys_get_temp_dir();
        // @codeCoverageIgnoreStart
        if (DIRECTORY_SEPARATOR === '\\') {
            return $tmpDir; // Windows: the temp dir is per-user and has no POSIX owner/mode
        }

        // @codeCoverageIgnoreEnd

        // The owner of a file created here is the user the directory must belong to (no ext-posix needed)
        $probe = tempnam($tmpDir, 'aop');
        $uid = $probe === false ? false : fileowner($probe);
        // @codeCoverageIgnoreStart
        if ($probe === false || $uid === false) {
            throw new NotWritableException("{$tmpDir} is not writable.");
        }

        // @codeCoverageIgnoreEnd

        @unlink($probe);
        $dir = sprintf('%s/ray-aop-%d', $tmpDir, $uid);
        if (! is_dir($dir) && ! is_link($dir)) {
            @mkdir($dir, 0700);
            clearstatcache(true, $dir);
            // @codeCoverageIgnoreStart
            if (! is_dir($dir)) {
                throw new NotWritableException("Could not create {$dir}.");
            }

            // @codeCoverageIgnoreEnd
        }

        clearstatcache(true, $dir);
        // fileowner() and fileperms() follow a symlink, so a link to a directory this user owns would pass them
        $isPrivate = ! is_link($dir) && fileowner($dir) === $uid && ((int) fileperms($dir) & 0022) === 0;
        if (! $isPrivate) {
            throw new NotWritableException("{$dir} must be a directory owned by the current user, not a symlink, and not writable by others.");
        }

        return $dir;
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
