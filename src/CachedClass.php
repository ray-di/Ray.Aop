<?php

declare(strict_types=1);

namespace Ray\Aop;

use function class_exists;
use function file_exists;

final class CachedClass
{
    /** @var class-string */
    public $class;

    /** @var string */
    public $path;

    /**
     * @param class-string $class
     */
    public function __construct(string $class, string $path)
    {
        $this->class = $class;
        $this->path = $path;
    }

    public function require(): bool
    {
        if (file_exists($this->path) && ! class_exists($this->path, false)) {
            require $this->path;

            return true;
        }

        return false;
    }
}
