<?php

declare(strict_types=1);

namespace Ray\Aop;

use Reflection;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionParameter;

use function array_merge;
use function implode;
use function is_numeric;
use function is_string;
use function sprintf;
use function var_export;

use const PHP_EOL;

final class MethodSignatureString
{
    private const NULLABLE = 'null|';
    private const INDENT = '    ';

    private readonly TypeString $typeString;

    public function __construct()
    {
        $this->typeString = new TypeString(self::NULLABLE);
    }

    /**
     * Returns a PSR12-friendly method signature: every line indented with 4 spaces.
     */
    public function get(ReflectionMethod $method): string
    {
        $lines = [];
        $docComment = $method->getDocComment();
        if (is_string($docComment)) {
            // Keep Reflection's exact docblock text so re-parsed getDocComment() matches the source
            $lines[] = $docComment;
        }

        foreach ($method->getAttributes() as $attribute) {
            $lines[] = self::INDENT . sprintf('#[%s]', $this->formatAttributeStr($attribute));
        }

        $signatureParts = $this->addAccessModifiers($method, []);
        $signatureParts = $this->addMethodSignature($method, $signatureParts);
        $lines[] = self::INDENT . implode(' ', $signatureParts);

        return implode(PHP_EOL, $lines);
    }

    /** @param ReflectionAttribute<object> $attribute */
    private function formatAttributeStr(ReflectionAttribute $attribute): string
    {
        $argsList = $attribute->getArguments();
        $formattedArgs = [];
        /** @var scalar $value */
        foreach ($argsList as $name => $value) {
            $formattedArgs[] = $this->formatArg($name, $value);
        }

        return sprintf('\\%s(%s)', $attribute->getName(), implode(', ', $formattedArgs));
    }

    /**
     * @param list<string> $signatureParts
     *
     * @return list<string>
     */
    private function addAccessModifiers(ReflectionMethod $method, array $signatureParts): array
    {
        $modifier = implode(' ', Reflection::getModifierNames($method->getModifiers()));

        return array_merge($signatureParts, [$modifier]);
    }

    /**
     * @param list<string> $signatureParts
     *
     * @return list<string>
     */
    private function addMethodSignature(ReflectionMethod $method, array $signatureParts): array
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $params[] = $this->generateParameterCode($param);
        }

        $parmsList = implode(', ', $params);
        $rType = $method->getReturnType();
        $return = $rType ? ': ' . ($this->typeString)($rType) : '';

        $signatureParts[] = sprintf('function %s(%s)%s', $method->getName(), $parmsList, $return);

        return $signatureParts;
    }

    /**
     * @param string|int $name
     * @param mixed      $value
     *
     * @psalm-external-mutation-free
     */
    private function formatArg($name, $value): string
    {
        $formattedValue = var_export($value, true);

        return is_numeric($name) ? $formattedValue : "{$name}: {$formattedValue}";
    }

    private function generateParameterCode(ReflectionParameter $param): string
    {
        $attributesStr = $this->getAttributeStr($param);
        $typeStr = ($this->typeString)($param->getType());
        $typeStrWithSpace = $typeStr ? $typeStr . ' ' : $typeStr;
        $variadicStr = $param->isVariadic() ? '...' : '';
        $referenceStr = $param->isPassedByReference() ? '&' : '';
        $defaultStr = '';
        if ($param->isDefaultValueAvailable()) {
            $defaultStr = ' = ' . var_export($param->getDefaultValue(), true);
        }

        return "{$attributesStr}{$typeStrWithSpace}{$referenceStr}{$variadicStr}\${$param->getName()}{$defaultStr}";
    }

    public function getAttributeStr(ReflectionParameter $param): string
    {
        $attributesStr = '';
        $attributes = $param->getAttributes();
        if (! empty($attributes)) {
            $attributeStrings = [];
            foreach ($attributes as $attribute) {
                $attributeStrings[] = sprintf('#[%s]', $this->formatAttributeStr($attribute));
            }

            $attributesStr = implode(' ', $attributeStrings) . ' ';
        }

        return $attributesStr;
    }
}
