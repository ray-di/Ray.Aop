<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use InvalidArgumentException;

class InvalidAnnotationException extends InvalidArgumentException implements ExceptionInterface
{
}
