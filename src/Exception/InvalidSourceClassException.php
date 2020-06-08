<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use LogicException;

class InvalidSourceClassException extends LogicException implements ExceptionInterface
{
}
