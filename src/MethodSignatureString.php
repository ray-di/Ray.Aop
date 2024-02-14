<?php

declare(strict_types=1);

namespace Ray\Aop;

use Reflection;
use ReflectionMethod;
use ReflectionParameter;

use function implode;
use function is_numeric;
use function preg_replace;
use function sprintf;
use function str_replace;
use function var_export;

use const PHP_EOL;
use const PHP_MAJOR_VERSION;

/** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
final class MethodSignatureString
{
    /** @var TypeString */
    private $typeString;

    public function __construct(int $phpVersion)
    {
        $nullableStr = $phpVersion >= 80000 ? 'null|' : '?';
        $this->typeString = new TypeString($nullableStr);
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
            $returnType = ': ' . ($this->typeString)($rType);
        }

        $parmsList = implode(', ', $params);

        $signatureParts[] = sprintf('function %s(%s)%s', $method->getName(), $parmsList, $returnType);

        return implode(' ', $signatureParts);
    }

    public function generateParameterCode(ReflectionParameter $param): string
    {
        $typeStr = ($this->typeString)($param->getType());
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
}
