<?php

declare(strict_types=1);

namespace Ray\Aop;

use Override;
use ReturnTypeWillChange;

use function array_map;
use function get_class_methods;

/**
 * @template T of object
 * @template-extends \ReflectionClass<T>
 */
final class ReflectionClass extends \ReflectionClass
{
    /**
     * Get all attributes as instantiated objects
     *
     * @return list<object>
     */
    public function getAnnotations(): array
    {
        $attributes = $this->getAttributes();

        return array_map(
            static fn ($attribute) => $attribute->newInstance(),
            $attributes
        );
    }

    /**
     * Get a specific attribute by name
     *
     * @param class-string<TAnnotation> $annotationName
     *
     * @return TAnnotation|null
     *
     * @template TAnnotation of object
     */
    public function getAnnotation(string $annotationName): object|null
    {
        $attributes = $this->getAttributes($annotationName);
        if (isset($attributes[0])) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    /**
     * @param int|null $filter
     *
     * @return list<ReflectionMethod>
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getMethods($filter = null): array
    {
        unset($filter);
        $methods = [];
        $methodNames = get_class_methods($this->name);
        foreach ($methodNames as $methodName) {
            $methods[] = new ReflectionMethod($this->name, $methodName);
        }

        return $methods;
    }

    /** @psalm-external-mutation-free */
    #[Override]
    public function getConstructor(): \ReflectionMethod|null
    {
        $parent = parent::getConstructor();
        if ($parent === null) {
            return null;
        }

        return new ReflectionMethod($parent->class, $parent->name);
    }

    /**
     * @return ReflectionClass<object>|false
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    #[ReturnTypeWillChange]
    public function getParentClass()
    {
        $parent = \ReflectionClass::getParentClass();

        return $parent instanceof \ReflectionClass ? (new ReflectionClass($parent->getName())) : false;
    }
}
