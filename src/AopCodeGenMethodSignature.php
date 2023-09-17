<?php

declare(strict_types=1);

namespace Ray\Aop;

use Reflection;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function class_exists;
use function implode;
use function sprintf;
use function str_replace;
use function var_export;

use const PHP_EOL;
use const PHP_VERSION_ID;

final class AopCodeGenMethodSignature
{
    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedMethodCall
     */
    public function get(ReflectionMethod $method): string
    {
        $signatureParts = [];

        // PHPDocを取得
        if ($docComment = $method->getDocComment()) {
            $signatureParts[] = $docComment . PHP_EOL;
        }

        // アトリビュートを取得 (PHP 8.0+ の場合のみ)
        if (PHP_VERSION_ID >= 80000) {
            /** @psalm-suppress MixedAssignment */
            foreach ($method->getAttributes() as $attribute) {
                $args = array_map(/** @param mixed $arg */static function ($arg): string {
                    return var_export($arg, true);
                }, $attribute->getArguments());

                $signatureParts[] = sprintf('    #[\\%s(%s)]', $attribute->getName(), implode(', ', $args)) . PHP_EOL;
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
        if ($rType = $method->getReturnType()) {
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
        $typeStr = '';

        if ($type instanceof ReflectionNamedType) {
            $typeStr = $type->isBuiltin() ? $type->getName() : '\\' . $type->getName();
        } elseif (class_exists('ReflectionUnionType') && $type instanceof ReflectionUnionType) {
            $types = array_map(static function (ReflectionNamedType $t) {
                return $t->isBuiltin() ? $t->getName() : '\\' . $t->getName();
            }, (array) $type->getTypes());

            return implode('|', $types);
        }

        // 単一型の Nullableのチェックをユニオンタイプのチェックの後に移動
        if ($type && $type->allowsNull()) {
            $typeStr = 'null|' . $typeStr;
        }

        return $typeStr;
    }
}
