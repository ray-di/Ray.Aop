<?php

declare(strict_types=1);

namespace Ray\Aop;

final class AopClassName
{
    /**
     * @var string
     */
    private $classDir;

    public function __construct(string $classDir)
    {
        $this->classDir = $classDir;
    }

    public function __invoke(string $class, string $bindName) : string
    {
        return sprintf('%s_%s', $class, crc32($bindName . $this->classDir));
    }
}
