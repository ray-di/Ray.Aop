<?php
namespace Ray\Aop;

/**
 * @FakeResource
 * @FakeClassAnnotation
 */
class FakePhp71ReturnTypeClass
{
    public function returnTypeVoid() : void
    {
    }

    public function returnNullable() : ?int
    {
        return null;
    }
}
