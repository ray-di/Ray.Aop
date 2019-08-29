<?php

declare(strict_types=1);
namespace Ray\Aop;

use Composer\Autoload;

class FakePhp71NullableClass
{
    public $returnTypeVoidCalled = false;

    public function returnTypeVoid() : void
    {
        new Autoload\ClassLoader();
        $this->returnTypeVoidCalled = true;
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
        return $ids[0];
    }

    public function typed(\SplObjectStorage $storage)
    {
    }

    public function useTyped(CodeGen $codeGen)
    {
    }
}
