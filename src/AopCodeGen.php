<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;

use function file_exists;
use function file_get_contents;
use function token_get_all;

use const PHP_VERSION_ID;
use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

final class AopCodeGen
{
    /**
     * Generate AOP class
     *
     * Powered by PHP token and reflection
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix): string
    {
        $fileName = (string) $sourceClass->getFileName();
        if (! file_exists($fileName)) {
            throw new InvalidSourceClassException($sourceClass->getName());
        }

        $code = (string) file_get_contents($fileName);

        // array<int, array{int, string, int}|string> in phpstorm
        // list<array{0: int, 1: string, 2: int}|string> in psalm
        /** @var array<int, array{int, string, int}|string> $tokens */
        $tokens = token_get_all($code);

        $inClass = false;
        $className = '';
        $newCode = new GeneratedCode(new MethodSignatureString(PHP_VERSION_ID));
        $iterator = new TokenIterator($tokens);

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            [$id, $text] = $iterator->getToken();
            $isClassKeyword = $id === T_CLASS;
            if ($isClassKeyword) {
                $inClass = true;
                $newCode->add($text);
                continue;
            }

            $isClassName = $inClass && $id === T_STRING && empty($className);
            if ($isClassName) {
                $className = $text;
                $newCode->addClassName($className, $postfix);
                continue;
            }

            $isExtendsKeyword = $id === T_EXTENDS;
            if ($isExtendsKeyword) {
                $iterator->skipExtends();
                continue;
            }

            $isClassSignatureEnds = $inClass && $text === '{';
            if ($isClassSignatureEnds) {
                $newCode->addIntercepterTrait();
                break;
            }

            $newCode->add($text);
        }

        $newCode->implementsInterface(WeavedInterface::class);
        $newCode->addMethods($sourceClass, $bind);

        return $newCode->getCodeText();
    }
}
