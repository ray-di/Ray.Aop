<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use LogicException;

final class InvalidSourceClassException extends LogicException implements ExceptionInterface
{
}
