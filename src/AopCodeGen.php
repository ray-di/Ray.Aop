<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;

use function file_exists;
use function file_get_contents;
use function is_array;
use function sprintf;
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
        $tokens = token_get_all($code);
        $inClass = false;
        $className = '';
        $newCode = new GeneratedCode(new MethodSignatureString(PHP_VERSION_ID));
        $iterator = new ArrayIterator($tokens);

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $token = $iterator->current();
            [$id, $text] = is_array($token) ? $token : [null, $token];

            $isClassKeyword = $id === T_CLASS;
            if ($isClassKeyword) {
                $inClass = true;
                $newCode->add($text . ' ');
                continue;
            }

            $isClassName = $inClass && $id === T_STRING && empty($className);
            if ($isClassName) {
                $className = $text;
                $newClassName = $className . $postfix;
                $newCode->add($newClassName . ' extends ' . $text . ' ');
                continue;
            }

            $isExtendsKeyword = $id === T_EXTENDS;
            if ($isExtendsKeyword) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip parent class name
                $iterator->next();  // Skip space
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
