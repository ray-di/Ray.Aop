<?php

declare(strict_types=1);
namespace Ray\Aop;

use Composer\Autoload;
use Reflection;

class FakePhp71ReturnTypeClass
{
    public function returnTypeVoid() : void
    {
    }

    public function returnNullable(string $str) : ?int
    {
        return null;
    }

    public function checkClass($a)
    {
        $a instanceof Autoload;
        $a instanceof Reflection;
    }
}
