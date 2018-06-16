<?php

declare(strict_types=1);
namespace Ray\Aop;

use Composer\Autoload;
use Reflection;

class FakePhp71NullableClass
{
    public function returnTypeVoid() : void
    {
    }

    public function returnNullable(string $str) : ?int
    {
        return null;
    }

    public function nullableParam(?int $id) : int
    {
    }
}
