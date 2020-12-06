<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use ReflectionClass;

use function file_exists;
use function fileatime;
use function mkdir;

final class CachedCompiler implements CompilerInterface
{
    /** @var Compiler */
    private $compiler;

    /** @var Cache */
    private $cache;

    /**
     * @throws AnnotationException
     */
    public function __construct(string $classDir, ?Cache $cache = null)
    {
        $this->compiler = new Compiler($classDir);
        if ($cache === null) {
            $tmpDir = $classDir . '/aop_cache';
            if (! file_exists($tmpDir)) {
                mkdir($tmpDir);
            }

            $cache = new PhpFileCache($tmpDir);
        }

        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        return $this->compiler->newInstance($class, $args, $bind);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $class, BindInterface $bind): string
    {
        $classPath = (string) (new ReflectionClass($class))->getFileName();
        $id = $bind->toString('') . fileatime($classPath);
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
