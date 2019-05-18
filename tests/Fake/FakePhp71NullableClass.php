<?php

declare(strict_types=1);
namespace Ray\Aop;

use Composer\Autoload;

class FakePhp71NullableClass
{
    public function returnTypeVoid() : void
    {
        new Autoload\ClassLoader();
    }

    public function returnNullable(string $str) : ?int
    {
        unset($str);
        return null;
    }

    public function nullableParam(?int $id, string $name = null) : ?int
    {
        return null;
    }

    public function variadicParam(int ...$ids)
    {
    }

    public function typed(\SplObjectStorage $storage)
    {
    }

    public function useTyped(CodeGen $codeGen)
    {
    }
}
