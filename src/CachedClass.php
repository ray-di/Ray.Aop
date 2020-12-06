<?php

declare(strict_types=1);

namespace Ray\Aop;

use function assert;
use function class_exists;
use function file_exists;
use function is_file;

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
        if (is_file($this->path) && ! class_exists($this->class, false)) {
            assert(file_exists($this->path)); // https://github.com/vimeo/psalm/issues/4788
            require $this->path;

            return true;
        }

        return false;
    }
}
