<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Cache\Cache;
use ReflectionClass;

use function fileatime;

final class CachedCompiler implements CompilerInterface
{
    /** @var Compiler */
    private $compiler;

    /** @var Cache */
    private $cache;

    /**
     * @throws AnnotationException
     */
    public function __construct(string $classDir, Cache $cache)
    {
        $this->compiler = new Compiler($classDir);
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        $compiledClass = $this->compile($class, $bind);
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        if (isset($instance->bindings)) {
            $instance->bindings = $bind->getBindings();
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $class, BindInterface $bind): string
    {
        $classPath = (string) (new ReflectionClass($class))->getFileName();
        $id = $bind->toString('') . fileatime($classPath) . $class;
        /** @var ?CachedClass $cachedClass */
        $cachedClass = $this->cache->fetch($id);
        if ($cachedClass instanceof CachedClass && $cachedClass->require()) {
            return $cachedClass->class;
        }

        $compiledName = $this->compiler->compile($class, $bind);
        $compiledPath = (string) (new ReflectionClass($compiledName))->getFileName();
        $this->cache->save($id, new CachedClass($compiledName, $compiledPath));

        return $compiledName;
    }
}
