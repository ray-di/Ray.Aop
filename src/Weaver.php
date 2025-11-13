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

    /** @param ScriptDir $classDir */
    public function __construct(private readonly BindInterface $bind, private readonly string $classDir)
    {
        /** @psalm-suppress PropertyTypeCoercion */
        /** @phpstan-ignore-next-line assign.propertyType */
        $this->bindName = (string) $this->bind;
        $this->compiler = new Compiler($classDir);
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
        $instance = (new ReflectionClass($aopClass))->newInstanceArgs($args);
        if (! $instance instanceof WeavedInterface) {
            return $instance;
        }

        $instance->_setBindings($this->bind->getBindings());
        assert($instance instanceof $class);

        return $instance;
    }

    /**
     * @param class-string $class
     *
     * @return class-string
     */
    public function weave(string $class): string
    {
        $aopClass = new AopPostfixClassName($class, $this->bindName, $this->classDir);
        if (class_exists($aopClass->fqn, false)) {
            return $aopClass->fqn;
        }

        if ($this->loadClass($aopClass->fqn)) {
            assert(class_exists($aopClass->fqn));

            return $aopClass->fqn;
        }

        $newClass = $this->compiler->compile($class, $this->bind);
        assert(class_exists($newClass));

        return $newClass;
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
