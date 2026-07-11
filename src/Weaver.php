<?php

declare(strict_types=1);

namespace Ray\Aop;

use function assert;
use function class_exists;
use function file_exists;
use function sprintf;
use function str_replace;

/**
 * @psalm-import-type BindingName from Types
 * @psalm-import-type ScriptDir from Types
 */
final class Weaver
{
    /** @var BindingName */
    private readonly string $bindName;
    private readonly Compiler $compiler;

    /** @var array<class-string, class-string> */
    private array $classCache = [];

    /** @param ScriptDir $classDir */
    public function __construct(private readonly BindInterface $bind, private readonly string $classDir)
    {
        /** @psalm-suppress PropertyTypeCoercion */
        /** @phpstan-ignore-next-line assign.propertyType */
        $this->bindName = (string) $this->bind;
        $this->compiler = new Compiler($classDir);
    }

    /**
     * Exclude in-process FQN cache from serialization. A restored Weaver in another
     * process must re-run loadClass()/compile() — cached names would skip require and fatal.
     *
     * @return list<'bindName'|'compiler'|'bind'|'classDir'>
     */
    public function __sleep(): array
    {
        return ['bindName', 'compiler', 'bind', 'classDir'];
    }

    /**
     * @param class-string<T> $class
     * @param list<mixed>     $args
     *
     * @return T
     *
     * @template T of object
     */
    public function newInstance(string $class, array $args): object
    {
        $aopClass = $this->weave($class);
        /** @var T $instance */
        /** @var class-string<T> $aopClass */
        /** @psalm-suppress MixedMethodCall */
        $instance = new $aopClass(...$args);
        assert($instance instanceof $class);
        if (! $instance instanceof WeavedInterface) {
            return $instance;
        }

        $instance->_setBindings($this->bind->getBindings());

        return $instance;
    }

    /**
     * @param class-string $class
     *
     * @return class-string
     */
    public function weave(string $class): string
    {
        if (isset($this->classCache[$class])) {
            return $this->classCache[$class];
        }

        $aopClass = new AopPostfixClassName($class, $this->bindName, $this->classDir);
        if (class_exists($aopClass->fqn, false)) {
            return $this->classCache[$class] = $aopClass->fqn;
        }

        if ($this->loadClass($aopClass->fqn)) {
            assert(class_exists($aopClass->fqn));

            return $this->classCache[$class] = $aopClass->fqn;
        }

        $newClass = $this->compiler->compile($class, $this->bind);
        assert(class_exists($newClass));

        return $this->classCache[$class] = $newClass;
    }

    private function loadClass(string $class): bool
    {
        $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $class));
        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }
}
