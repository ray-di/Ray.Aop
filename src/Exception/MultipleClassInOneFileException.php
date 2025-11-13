<?php

declare(strict_types=1);

namespace Ray\Aop\Exception;

use LogicException;

final class MultipleClassInOneFileException extends LogicException implements ExceptionInterface
{
}
