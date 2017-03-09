<?php
namespace Ray\Aop;

/**
 * @FakeResource
 * @FakeClassAnnotation
 */
class FakePhp7ReturnTypeClass
{
    public function returnTypeArray() : array
    {
        return [1, 2, 3];
    }

    public function returnTypeBool() : bool
    {
        return true;
    }

    public function returnTypeFloat() : float
    {
        return 1.234;
    }

    public function returnTypeInteger() : int
    {
        return 1;
    }

    public function returnTypeString() : string
    {
        return 'this is string';
    }
}
