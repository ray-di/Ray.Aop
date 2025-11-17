<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionClass;

use function crc32;
use function filemtime;

/**
 * Fully qualified name including postfix
 */
final class AopPostfixClassName
{
    public readonly string $fqn;
    public readonly string $postFix;

    /** @param class-string $class */
    public function __construct(string $class, string $bindings, string $classDir)
    {
        $fileTime = (string) filemtime((string) (new ReflectionClass($class))->getFileName());
        $this->postFix = '_' . crc32($fileTime . $bindings . $classDir);
        $this->fqn = $class . $this->postFix;
    }
}
