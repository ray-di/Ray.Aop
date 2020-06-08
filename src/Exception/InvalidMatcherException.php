<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use InvalidArgumentException;

class InvalidMatcherException extends InvalidArgumentException implements ExceptionInterface
{
}
