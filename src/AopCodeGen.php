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
     * PHP token for class and reflection method
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix): string
    {
        $code = new AopCode(new MethodSignatureString(PHP_VERSION_ID));

        return $code->generate($sourceClass, $bind, $postfix);
    }
}
