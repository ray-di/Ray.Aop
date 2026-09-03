<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\NotWritableException;

use function chmod;
use function dirname;
use function file_put_contents;
use function is_int;
use function is_string;
use function rename;
use function tempnam;
use function umask;
use function unlink;

/**
 * Atomic write: a process sharing the script dir sees either no file or the whole file
 */
final class FilePutContents
{
    public function __invoke(string $file, string $content): void
    {
        $tmp = tempnam(dirname($file), 'swap');
        if (is_string($tmp) && is_int(file_put_contents($tmp, $content)) && chmod($tmp, 0666 & ~umask()) && @rename($tmp, $file)) {
            return;
        }

        @unlink((string) $tmp);

        throw new NotWritableException($file);
    }
}
