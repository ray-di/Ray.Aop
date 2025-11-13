<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use RuntimeException;

final class NotWritableException extends RuntimeException implements ExceptionInterface
{
}
