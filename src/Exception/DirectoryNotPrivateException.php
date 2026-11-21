<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use RuntimeException;

/**
 * Thrown when the per-user script directory is a symlink, owned by another user, or writable by group or others.
 *
 * Message format: {directory path}
 */
final class DirectoryNotPrivateException extends RuntimeException implements ExceptionInterface
{
}
