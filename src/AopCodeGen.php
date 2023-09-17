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

use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

final class AopCodeGen
{
    /** @var AopCodeGenMethodSignature */
    private $methodSignature;

    public function __construct()
    {
        $this->methodSignature = new AopCodeGenMethodSignature();
    }

    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix = '_aop'): string
    {
        $fileName = $sourceClass->getFileName();
        if (! file_exists((string) $fileName)) {
            throw new InvalidSourceClassException($sourceClass->getName());
        }

        $code = file_get_contents($fileName);
        $tokens = token_get_all($code);
        $inClass = false;
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

            if ($inClass && $text === '{') {
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

            $signature = $this->methodSignature->get($method);
            $isVoid = false;
            if ($method->hasReturnType() && (! $method->getReturnType() instanceof ReflectionUnionType)) {
                $nt = $method->getReturnType();
                $isVoid = $nt instanceof ReflectionNamedType && $nt->getName()  === 'void';
            }

            $return = $isVoid ? '' : 'return ';
            $additionalMethods[] = sprintf("    %s\n    {\n        %s%s\n    }\n", $signature, $return, $statement);
        }

        if ($additionalMethods) {
            $newCode->insert(implode("\n", $additionalMethods));
        }
    }
}
