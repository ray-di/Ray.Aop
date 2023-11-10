<?php

declare(strict_types=1);

namespace Ray\Aop;

use Reflection;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function assert;
use function class_exists;
use function implode;
use function is_numeric;
use function preg_replace;
use function sprintf;
use function str_replace;
use function var_export;

use const PHP_EOL;
use const PHP_MAJOR_VERSION;

/** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
final class AopCodeGenMethodSignature
{
    /** @var string */
    private $nullableStr;

    public function __construct(int $phpVersion)
    {
        $this->nullableStr = $phpVersion >= 80000 ? 'null|' : '?';
    }

    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedMethodCall
     */
    public function get(ReflectionMethod $method): string
    {
        $signatureParts = [];

        // PHPDocを取得
        $docComment = $method->getDocComment();
        if ($docComment) {
            $signatureParts[] = $docComment . PHP_EOL;
        }

        // アトリビュートを取得 (PHP 8.0+ の場合のみ)
        if (PHP_MAJOR_VERSION >= 8) {
            /** @psalm-suppress MixedAssignment */
            foreach ($method->getAttributes() as $attribute) {
                $argsList = $attribute->getArguments();
                $formattedArgs = [];

                foreach ($argsList as $name => $value) {
                    $formattedValue = preg_replace('/\s+/', ' ', var_export($value, true));
                    $argRepresentation = is_numeric($name) ? $formattedValue : "{$name}: {$formattedValue}";
                    $formattedArgs[] = $argRepresentation;
                }

                $signatureParts[] = sprintf('    #[\\%s(%s)]', $attribute->getName(), implode(', ', $formattedArgs)) . PHP_EOL;
            }
        }

        if ($signatureParts) {
            $signatureParts[] = '    '; // インデント追加
        }

        // アクセス修飾子を取得
        $modifier = implode(' ', Reflection::getModifierNames($method->getModifiers()));
        $signatureParts[] = $modifier;

        // メソッド名とパラメータを取得
        $params = [];
        foreach ($method->getParameters() as $param) {
            $params[] = $this->generateParameterCode($param);
        }

        $returnType = '';
        $rType = $method->getReturnType();
        if ($rType) {
            $returnType = ': ' . $this->getTypeString($rType);
        }

        $parmsList = implode(', ', $params);

        $signatureParts[] = sprintf('function %s(%s)%s', $method->getName(), $parmsList, $returnType);

        return implode(' ', $signatureParts);
    }

    public function generateParameterCode(ReflectionParameter $param): string
    {
        $typeStr = $this->getTypeString($param->getType());
        $typeStrWithSpace = $typeStr ? $typeStr . ' ' : $typeStr;
        // Variadicのチェック
        $variadicStr = $param->isVariadic() ? '...' : '';

        // 参照渡しのチェック
        $referenceStr = $param->isPassedByReference() ? '&' : '';

        // デフォルト値のチェック
        $defaultStr = '';
        if ($param->isDefaultValueAvailable()) {
            $default = var_export($param->getDefaultValue(), true);
            $defaultStr = ' = ' . str_replace(["\r", "\n"], '', $default);
        }

        return "{$typeStrWithSpace}{$referenceStr}{$variadicStr}\${$param->getName()}{$defaultStr}";
    }

    public function getTypeString(?ReflectionType $type): string
    {
        if (! $type) {
            return '';
        }

        // PHP 8.0+
        if (class_exists('ReflectionUnionType') && $type instanceof ReflectionUnionType) {
            $types = array_map(/** @param ReflectionNamedType|ReflectionNamedType $t */static function ($t) {
                if ($t instanceof ReflectionIntersectionType) {
                    $intersectionTypes = array_map(/** @param ReflectionNamedType $t */ static function ($t) {
                        return self::getFqnType($t);
                    }, $t->getTypes());

                    return sprintf('(%s)', implode('&', $intersectionTypes));
                }

                return self::getFqnType($t);
            }, (array) $type->getTypes());

            return implode('|', $types);
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

    public function intersectionTypeToString(ReflectionIntersectionType $intersectionType): string
    {
        $types = $intersectionType->getTypes();
        $typeStrings = array_map(static function ($type) {
            return '\\' . $type->getName();
        }, $types);

        return implode(' & ', $typeStrings);
    }

    public static function getFqnType(ReflectionNamedType $type): string
    {
        $isBuiltinOrSelf = $type->isBuiltin() || $type->getName() === 'self';

        return $isBuiltinOrSelf ? $type->getName() : '\\' . $type->getName();
    }
}
