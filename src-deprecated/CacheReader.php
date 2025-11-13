<?php

declare(strict_types=1);

namespace Ray\ServiceLocator;

use Koriym\Attributes\AttributeReaderInterface;
use LogicException;
use Override;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

use function array_map;
use function array_merge;
use function assert;
use function filemtime;
use function is_string;
use function max;
use function rawurlencode;

/**
 * Minimal cache aware annotation reader
 *
 * This code is taken from original PsrCachedReader.php in doctrine/annotation and modified.
 *
 * @see https://github.com/doctrine/annotations/commits/2.0.x/lib/Doctrine/Common/Annotations/PsrCachedReader.php
 *
 * Many thanks to the Doctrine team for their great contributions to the PHP community over the years.
 * @deprecated Use AttributeReaderInterface directly with your own caching layer.
 * @psalm-suppress DeprecatedClass
 */
final class CacheReader implements AttributeReaderInterface
{
    /** @var AttributeReaderInterface */
    private $delegate;

    /** @var array<string, array<object>> */
    private $loadedAnnotations = [];

    /** @var int[] */
    private $loadedFilemtimes = [];

    public function __construct(AttributeReaderInterface $reader, private readonly Cache $cache)
    {
        $this->delegate = $reader;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getClassAnnotations(ReflectionClass $class): array
    {
        $cacheKey = $class->getName();

        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }

        $annots = $this->fetchFromCache($cacheKey, $class, __FUNCTION__, $class);

        return $this->loadedAnnotations[$cacheKey] = $annots;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getClassAnnotation(ReflectionClass $class, string $annotationName): ?object
    {
        foreach ($this->getClassAnnotations($class) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getPropertyAnnotations(ReflectionProperty $property): array
    {
        throw new LogicException(__FUNCTION__ . ' Not Supported');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getPropertyAnnotation(ReflectionProperty $property, string $annotationName): ?object
    {
        throw new LogicException(__FUNCTION__ . ' Not Supported');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        $class    = $method->getDeclaringClass();
        $cacheKey = $class->getName() . '#' . $method->getName();

        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }

        $annots = $this->fetchFromCache($cacheKey, $class, __FUNCTION__, $method);

        return $this->loadedAnnotations[$cacheKey] = $annots;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getMethodAnnotation(ReflectionMethod $method, string $annotationName): ?object
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    /**
     * @return array<object>
     *
     * @psalm-suppress MixedInferredReturnType
     */
    private function fetchFromCache(  // @phpstan-ignore-line
        string $cacheKey,
        ReflectionClass $class,
        string $method,
        Reflector $reflector
    ): array {
        $cacheKey = rawurlencode($cacheKey) . $this->getLastModification($class);

        return $this->cache->get(
            $cacheKey,
            /** @return array<object> */
            function () use ($method, $reflector): array {
                /** @var array<object> $annotations */
                $annotations = $this->delegate->{$method}($reflector);

                return $annotations;
            }
        );
    }

    /**
     * Returns the time the class was last modified, testing traits and parents
     */
    private function getLastModification(ReflectionClass $class): int  // @phpstan-ignore-line
    {
        $filename = $class->getFileName();

        if (isset($this->loadedFilemtimes[$filename])) {
            return $this->loadedFilemtimes[$filename];
        }

        $parent = $class->getParentClass();

        $lastModification =  max(array_merge(
            [is_string($filename) ? filemtime($filename) : 0],
            array_map($this->getTraitLastModificationTime(...), $class->getTraits()),
            array_map($this->getLastModification(...), $class->getInterfaces()),
            $parent ? [$this->getLastModification($parent)] : []
        ));

        assert($lastModification !== false);

        return $this->loadedFilemtimes[$filename] = $lastModification;
    }

    private function getTraitLastModificationTime(ReflectionClass $reflectionTrait): int  // @phpstan-ignore-line
    {
        $fileName = $reflectionTrait->getFileName();

        if (isset($this->loadedFilemtimes[$fileName])) {
            return $this->loadedFilemtimes[$fileName];
        }

        $lastModificationTime = max(array_merge(
            [is_string($fileName) ? filemtime($fileName) : 0],
            array_map($this->getTraitLastModificationTime(...), $reflectionTrait->getTraits())
        ));

        assert($lastModificationTime !== false);

        return $this->loadedFilemtimes[$fileName] = $lastModificationTime;
    }
}
