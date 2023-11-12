<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionClass;

use const PHP_VERSION_ID;

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
        $code = new GeneratedCode(new MethodSignatureString(PHP_VERSION_ID));
        $code->parseClass($sourceClass, $postfix);
        $code->implementsInterface(WeavedInterface::class);
        $code->addMethods($sourceClass, $bind);

        return $code->getCodeText();
    }
}
