<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function assert;
use function class_exists;
use function implode;
use function sprintf;

final class TypeString
{
    /** @var string */
    private $nullableStr;

    public function __construct(string $nullableStr)
    {
        $this->nullableStr = $nullableStr;
    }

    public function __invoke(?ReflectionType $type): string
    {
        if (! $type) {
            return '';
        }

        // PHP 8.0+
        if (class_exists('ReflectionUnionType') && $type instanceof ReflectionUnionType) {
            return $this->getUnionType($type);
        }

        if ($type instanceof ReflectionNamedType) {
            $typeStr = self::getFqnType($type);
            // Check for Nullable in single types
            if ($type->allowsNull() && $type->getName() !== 'null') {
                $typeStr = $this->nullableStr . $typeStr;
            }

            return $typeStr;
        }

        assert($type instanceof ReflectionIntersectionType);

        return $this->intersectionTypeToString($type);
    }

    private function intersectionTypeToString(ReflectionIntersectionType $intersectionType): string
    {
        $types = $intersectionType->getTypes();
        $typeStrings = array_map(static function ($type) {
            return '\\' . $type->getName();
        }, $types);

        return implode(' & ', $typeStrings);
    }

    public function getUnionType(ReflectionUnionType $type): string
    {
        $types = array_map(/** @param ReflectionNamedType|ReflectionIntersectionType $t */ static function ($t) {
            if ($t instanceof ReflectionIntersectionType) {
                $intersectionTypes = array_map(/** @param ReflectionNamedType $t */ static function ($t) {
                    return self::getFqnType($t);
                }, $t->getTypes());

                return sprintf('(%s)', implode('&', $intersectionTypes));
            }

            return self::getFqnType($t);
        }, $type->getTypes());

        return implode('|', $types);
    }

    private static function getFqnType(ReflectionNamedType $type): string
    {
        $isBuiltin = $type->isBuiltin() || in_array($type->getName(), ['static', 'self'], true);

        return $isBuiltin ? $type->getName() : '\\' . $type->getName();
    }
}
