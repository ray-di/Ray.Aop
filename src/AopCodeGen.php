<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Reflection;
use ReflectionMethod;
use ReflectionUnionType;

use function array_keys;
use function array_map;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function token_get_all;
use function var_export;
use function version_compare;

use const PHP_VERSION;
use const T_CLASS;
use const T_EXTENDS;
use const T_FUNCTION;
use const T_STRING;

class AopCodeGen
{
    public function generate(\ReflectionClass $sourceClass, string $postfix, BindInterface $bind, array $traits = [InterceptTrait::class], string $replacement = 'return $this->_intercept(func_get_args(), __FUNCTION__);}')
    {
        $code = file_get_contents($sourceClass->getFileName());
        $tokens = token_get_all($code);
        $inClass = false;
        $inMethod = false;
        $curlyBraceCount = 0;
        $methodStarted = false;
        $className = '';
        $firstBraceAfterClassHit = false;
        $newCode = new AopCodeGenNewCode();
        $iterator = new ArrayIterator($tokens);
        $bindings = array_keys($bind->getBindings());

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

            if ($text === '{' && $inClass && ! $firstBraceAfterClassHit) {
                $newCode->commit();
            }

            if ($inClass && $id === T_EXTENDS) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip class name
                $iterator->next();  // Skip space
                continue;
            }

            if ($text === ';' && $inClass && ! $inMethod) {
                $newCode->clear();
                continue;
            }

            if ($inClass && $text === '{' && ! $inMethod) {
                $newCode->add('{');
                if (! empty($traits)) {
                    $newCode->add(' use \\' . implode(', ', $traits) . '; ');
                    $newCode->commit();
                }

                continue;
            }

            if ($id === T_FUNCTION) {
                $inMethod = true;
                $methodStarted = false;
                $currentMethodName = $iterator->offsetGet($iterator->key() + 2)[1];
                if (! in_array($currentMethodName, $bindings)) {
                    $newCode->clear();
                    $newCode->ignore(true);
                }
            }

            if ($inMethod) {
                if ($text === '{') {
                    $curlyBraceCount++;
                    $methodStarted = true;
                } elseif ($text === '}') {
                    $curlyBraceCount--;
                    $newCode->ignore(false);
                }

                if ($methodStarted) {
                    if ($curlyBraceCount === 1 && $text === '{') {
                        $newCode->add('{ ' . $replacement . ' ');
                        continue;
                    }

                    if ($curlyBraceCount === 0) {
                        $newCode->commit();
                        $inMethod = false;
                        $methodStarted = false;
                        continue;
                    }

                    continue;  // We skip adding other contents inside the method
                }
            }

            $newCode->add($text);
        }

        $newCode->commit();
        $this->addParentClass($newCode, $sourceClass, $bind);

        return $newCode->code;
    }

    public function addParentClass(AopCodeGenNewCode $newCode, \ReflectionClass $sourceClass, BindInterface $bind): void
    {
        $parent = $sourceClass->getParentClass();
        if (! $parent) {
            return;
        }

//        $parentCode = $this->generate($parent, '__tmp', $bind);
//        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
//        file_put_contents($tempFile, $parentCode);
//        require $tempFile;
//        unlink($tempFile);
//        $parentClass = $parent->getName() . '__tmp';
//        class_exists($parentClass);
        $parentMethods = $parent->getMethods();
        foreach ($parentMethods as $method) {
            $signature = $this->getMethodSignature($method);
            $additionalMethods[] = sprintf("    %s\n    { return \$this->_intercept(func_get_args(), __FUNCTION__); }\n", $signature);
        }

        $newCode->insert(implode("\n", $additionalMethods));
        $newCode->commit();
    }

    private function prependBackslashIfNotPrimitive($type)
    {
        $primitives = ['int', 'float', 'bool', 'string', 'array', 'callable', 'iterable', 'mixed', 'void', 'object', 'self', 'parent'];
        if (in_array($type, $primitives, true)) {
            return $type;
        }

        return '\\' . $type;
    }

    private function getMethodSignature(ReflectionMethod $method)
    {
        $signatureParts = [];

        // PHPDocを取得
        if ($docComment = $method->getDocComment()) {
            $signatureParts[] = $docComment;
        }

        // アトリビュートを取得 (PHP 8.0+ の場合のみ)
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
            foreach ($method->getAttributes() as $attribute) {
                $args = array_map(static function ($arg) {
                    return var_export($arg, true);
                }, $attribute->getArguments());

                $signatureParts[] = sprintf('#[\\%s(%s)]', $attribute->getName(), implode(', ', $args));
            }
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
                    $types = array_map(function ($type) {
                        return $this->prependBackslashIfNotPrimitive($type->getName());
                    }, $paramType->getTypes());
                    $paramStr .= implode('|', $types) . ' ';
                } else {
                    $paramTypeStr = $this->prependBackslashIfNotPrimitive($paramType->getName());
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
                $returnType = ': ' . ($rType->allowsNull() ? '?' : '') . $this->prependBackslashIfNotPrimitive($rType->getName());
            }
        }

        $signatureParts[] = sprintf('function %s(%s)%s', $method->getName(), implode(', ', $params), $returnType);

        return implode(' ', $signatureParts);
    }
}
