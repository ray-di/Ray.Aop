<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;

use function array_keys;
use function array_map;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function ltrim;
use function sprintf;
use function token_get_all;
use function var_export;
use function version_compare;

use const PHP_EOL;
use const PHP_VERSION;
use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

class AopCodeGen
{
    public function generate(ReflectionClass $sourceClass, string $postfix, BindInterface $bind): string
    {
        $code = file_get_contents($sourceClass->getFileName());
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
                $newCode->commit();

                break;
            }

            $newCode->add($text);
        }

        $newCode->commit();
        $newCode->implementsInterface(WeavedInterface::class);
        $this->addMethods($newCode, $sourceClass, $bind);
        $newCode->finalyze();

        return $newCode->code;
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
            $isVoid = $method->hasReturnType() && $method->getReturnType()->getName() === 'void';
            $return = $isVoid ? '' : 'return ';
            $additionalMethods[] = sprintf("    %s\n    {\n        %s%s\n    }\n", $signature, $return, $statement);
        }

        if ($additionalMethods) {
            $newCode->insert(implode("\n", $additionalMethods));
        }

        $newCode->commit();
    }

    private function getMethodSignature(ReflectionMethod $method)
    {
        $signatureParts = [];

        // PHPDocを取得
        if ($docComment = $method->getDocComment()) {
            $signatureParts[] = $docComment . PHP_EOL;
        }

        // アトリビュートを取得 (PHP 8.0+ の場合のみ)
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
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
            $paramStr = '';

            // パラメータの型を取得
            if ($paramType = $param->getType()) {
                if (version_compare(PHP_VERSION, '8.0.0', '>=') && $paramType instanceof ReflectionUnionType) {
                    $types = array_map(function ($param) {
                        return $this->prependBackslashIfNotPrimitive($param);
                    }, $paramType->getTypes());
                    $paramStr .= implode('|', $types) . ' ';
                } else {
                    $paramTypeStr = $this->prependBackslashIfNotPrimitive($param);
                    $paramStr .= $paramTypeStr . ' ';
                }
            }

            // パラメータが参照渡しの場合
            if ($param->isPassedByReference()) {
                $paramStr .= '&';
            }

            // パラメータ名を取得
            $paramStr .= '$' . $param->getName();

            // デフォルト値を取得
            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $paramStr .= ' = ' . var_export($param->getDefaultValue(), true);
            }

            $params[] = $paramStr;
        }

        $returnType = '';
        if ($method->hasReturnType()) {
            $rType = $method->getReturnType();
            if (version_compare(PHP_VERSION, '8.0.0', '>=') && $rType instanceof ReflectionUnionType) {
                $types = array_map(static function ($type) {
                    return $type->prependBackslashIfNotPrimitive($type->getName());
                }, $rType->getTypes());
                $returnType = ': ' . ($rType->allowsNull() ? '?' : '') . implode('|', $types);
            } else {
                $returnType = ': ' . ($rType->allowsNull() ? '?' : '') . $this->prependBackslashIfNotPrimitiveFromTypeName($rType->getName());
            }
        }

        $signatureParts[] = sprintf('function %s(%s)%s', $method->getName(), implode(', ', $params), $returnType);

        return implode(' ', $signatureParts);
    }

    function prependBackslashIfNotPrimitive(ReflectionParameter $parameter)
    {
        $primitives = ['int', 'float', 'string', 'bool', 'array', 'callable', 'iterable', 'void', 'mixed', 'object', 'null', 'false', 'resource', 'static'];

        $type = $parameter->getType();
        if (! $type) {
            return '';
        }

        $typeName = $type->getName();

        // If it's a variadic parameter, add the ... prefix.
        $prefix = $parameter->isVariadic() ? ' ...' : '';

        if (in_array($typeName, $primitives)) {
            return $typeName . $prefix;
        }

        return $prefix . '\\' . ltrim($typeName, '\\');
    }

    private function prependBackslashIfNotPrimitiveFromTypeName($typeName)
    {
        $primitives = ['int', 'float', 'string', 'bool', 'array', 'callable', 'iterable', 'void', 'mixed', 'object', 'null', 'false', 'resource', 'static'];

        if (in_array($typeName, $primitives)) {
            return $typeName;
        }

        return '\\' . ltrim($typeName, '\\');
    }
}
