<?php

declare(strict_types=1);

namespace Ray\Aop;

use Composer\Autoload;
use Ray\Aop\Annotation\FakeMarker3;
use SplObjectStorage;

class FakePhp71NullableClass
{
    public $returnTypeVoidCalled = false;

    public function returnTypeVoid(): void
    {
        new Autoload\ClassLoader();
        $this->returnTypeVoidCalled = true;
    }

    public function returnNullable(string $str): ?int
    {
        unset($str);

        return null;
    }

    public function nullableParam(?int $id, ?string $name = null): ?int
    {
        return null;
    }

    public function variadicParam(int ...$ids)
    {
        return $ids[0];
    }

    public function typed(SplObjectStorage $storage): SplObjectStorage
    {
        return $storage;
    }

    public function useTyped(CodeGen $codeGen)
    {
    }

    /** @FakeMarker3 */
    #[FakeMarker3]
    public function attributed()
    {
    }
}
