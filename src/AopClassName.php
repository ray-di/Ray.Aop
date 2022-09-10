<?php

declare(strict_types=1);

namespace Ray\Aop;

use function crc32;
use function filemtime;
use function sprintf;

final class AopClassName
{
    /** @var string */
    private $classDir;

    public function __construct(string $classDir)
    {
        $this->classDir = $classDir;
    }

    /**
     * @param class-string $class
     */
    public function __invoke(string $class, string $bindings): string
    {
        $fileTime = filemtime((string) (new ReflectionClass($class))->getFileName());

        return sprintf('%s_%s', $class, crc32($fileTime . $bindings . $this->classDir));
    }
}
