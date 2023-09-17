<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Ray\Aop\Exception\InvalidSourceClassException;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

use function array_keys;
use function array_map;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function str_replace;
use function token_get_all;
use function var_export;

use const PHP_EOL;
use const PHP_VERSION_ID;
use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

final class AopCodeGen
{
    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix = '_aop'): string
    {
        $fileName = $sourceClass->getFileName();
        if (! file_exists((string) $fileName)) {
            throw new InvalidSourceClassException($sourceClass->getName());
        }

        $code = file_get_contents($fileName);
        $tokens = token_get_all($code);
        $inClass = false;
        $inMethod = false;
        $className = '';
        $newCode = new AopCodeGenNewCode();
        $iterator = new ArrayIterator($tokens);

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $token = $iterator->current();
            [$id, $text] = is_array($token) ? $token : [null, $token];

            if ($id === T_CLASS) {
                $inClass = true;
                $newCode->add($text . ' ');
                continue;
            }

            if ($inClass && $id === T_STRING && empty($className)) {
                $className = $text;
                $newClassName = $text . $postfix;
                $newCode->add($newClassName . ' extends ' . $className . ' ');
                continue;
            }

            if ($inClass && $id === T_EXTENDS) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip class name
                $iterator->next();  // Skip space
                continue;
            }

            if ($inClass && $text === '{' && ! $inMethod) {
                $newCode->add(sprintf("{\n    use \%s;\n}\n", InterceptTrait::class));

                break;
            }

            $newCode->add($text);
        }

        $newCode->implementsInterface(WeavedInterface::class);
        $this->addMethods($newCode, $sourceClass, $bind);
        $newCode->getCodeText();

        return $newCode->getCodeText();
    }

    private function addMethods(AopCodeGenNewCode $newCode, ReflectionClass $class, BindInterface $bind): void
    {
        $bindings = array_keys($bind->getBindings());

        $parentMethods = $class->getMethods();
        $statement = '\$this->_intercept(func_get_args(), __FUNCTION__);';
        $additionalMethods = [];
        foreach ($parentMethods as $method) {
            if (! in_array($method->getName(), $bindings)) {
                continue;
            }

            $signature = $this->getMethodSignature($method);
            $isVoid = $method->hasReturnType() && (! $method->getReturnType() instanceof ReflectionUnionType) && $method->getReturnType()->getName()  === 'void';
            $return = $isVoid ? '' : 'return ';
            $additionalMethods[] = sprintf("    %s\n    {\n        %s%s\n    }\n", $signature, $return, $statement);
        }

        if ($additionalMethods) {
            $newCode->insert(implode("\n", $additionalMethods));
        }

    }

    private function getMethodSignature(ReflectionMethod $method)
    {
        $signatureParts = [];

        // PHPDocを取得
        if ($docComment = $method->getDocComment()) {
            $signatureParts[] = $docComment . PHP_EOL;
        }

        // アトリビュートを取得 (PHP 8.0+ の場合のみ)
        if (PHP_VERSION_ID >= 80000) {
            foreach ($method->getAttributes() as $attribute) {
                $args = array_map(static function ($arg) {
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

    function generateParameterCode(ReflectionParameter $param): string
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
            }, $type->getTypes());

            return implode('|', $types);
        }

        // 単一型の Nullableのチェックをユニオンタイプのチェックの後に移動
        if ($type && $type->allowsNull()) {
            $typeStr = 'null|' . $typeStr;
        }

        return $typeStr;
    }
}
