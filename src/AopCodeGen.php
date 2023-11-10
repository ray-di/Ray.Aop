<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

use function array_keys;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function token_get_all;

use const PHP_VERSION_ID;
use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

final class AopCodeGen
{
    public const INTERCEPT_STATEMENT = '\$this->_intercept(__FUNCTION__, func_get_args());';
    /** @var MethodSignatureString */
    private $methodSignature;

    public function __construct()
    {
        $this->methodSignature = new MethodSignatureString(PHP_VERSION_ID);
    }

    /**
     * Generate AOP class
     *
     * Powered by PHP token and reflection
     *
     * @param ReflectionClass<object> $sourceClass
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix): string
    {
        $fileName = (string) $sourceClass->getFileName();
        if (! file_exists($fileName)) {
            throw new InvalidSourceClassException($sourceClass->getName());
        }

        $code = (string) file_get_contents($fileName);
        $tokens = token_get_all($code);
        $inClass = false;
        $className = '';
        $newCode = new GeneratedCode();
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

            if ($inClass && $text === '{') {
                $newCode->add(sprintf("{\n    use \%s;\n}\n", InterceptTrait::class));

                break;
            }

            $newCode->add($text);
        }

        $newCode->implementsInterface(WeavedInterface::class);
        $this->addMethods($newCode, $sourceClass, $bind);

        return $newCode->getCodeText();
    }

    /** @param ReflectionClass<object> $class */
    private function addMethods(GeneratedCode $newCode, ReflectionClass $class, BindInterface $bind): void
    {
        $bindings = array_keys($bind->getBindings());

        $parentMethods = $class->getMethods();
        $interceptedMethods = [];
        foreach ($parentMethods as $method) {
            if (! in_array($method->getName(), $bindings)) {
                continue;
            }

            $signature = $this->methodSignature->get($method);
            $isVoid = false;
            if ($method->hasReturnType() && (! $method->getReturnType() instanceof ReflectionUnionType)) {
                $nt = $method->getReturnType();
                $isVoid = $nt instanceof ReflectionNamedType && $nt->getName()  === 'void';
            }

            $return = $isVoid ? '' : 'return ';
            $interceptedMethods[] = sprintf("    %s\n    {\n        %s%s\n    }\n", $signature, $return, self::INTERCEPT_STATEMENT);
        }

        if (! $interceptedMethods) {
            return;
        }

        $newCode->insert(implode("\n", $interceptedMethods));
    }
}
