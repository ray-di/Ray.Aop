<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function assert;
use function implode;
use function sprintf;

/**
 * TypeString converts ReflectionType instances to their corresponding string representations.
 */
final class TypeString
{
    public function __construct(private readonly string $nullableStr)
    {
    }

    /** @psalm-external-mutation-free */
    public function __invoke(ReflectionType|null $type): string
    {
        if (! $type) {
            return '';
        }

        if ($type instanceof ReflectionUnionType) {
            return $this->getUnionType($type);
        }

        if ($type instanceof ReflectionNamedType) {
            $typeStr = self::getFqnType($type);
            // Check for Nullable in single types
            if ($typeStr !== 'mixed' && $type->allowsNull() && $type->getName() !== 'null') {
                $typeStr = $this->nullableStr . $typeStr;
            }

            return $typeStr;
        }

        assert($type instanceof ReflectionIntersectionType);

        return $this->intersectionTypeToString($type);
    }

    /** @psalm-pure */
    private function intersectionTypeToString(ReflectionIntersectionType $intersectionType): string
    {
        $types = $intersectionType->getTypes();
        $typeStrings = [];

        /** @var ReflectionNamedType $type */
        foreach ($types as $type) {
            $typeStrings[] = '\\' . $type->getName();
        }

        return implode(' & ', $typeStrings);
    }

    public function getUnionType(ReflectionUnionType $type): string
    {
        $types = array_map(static function ($t) {
            /** @psalm-suppress DocblockTypeContradiction */
            if ($t instanceof ReflectionIntersectionType) {
                $types = $t->getTypes();
                /** @var array<ReflectionNamedType>  $types */
                $intersectionTypes = array_map(self::getFqnType(...), $types);

                return sprintf('(%s)', implode('&', $intersectionTypes));
            }

            return self::getFqnType($t);
        }, $type->getTypes());

        return implode('|', $types);
    }

    private static function getFqnType(ReflectionNamedType $namedType): string
    {
        $type = $namedType->getName();
        $isBuiltin = $namedType->isBuiltin() || $type === 'static' || $type === 'self';

        return $isBuiltin ? $type : '\\' . $type;
    }
}
