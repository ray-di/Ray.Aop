<?php
namespace Ray\Aop;

/**
 * @FakeResource
 * @FakeClassAnnotation
 */
class FakePhp7Class
{
    public function run(string $a, int $b, float $c, bool $d) : array
    {
        return [$a, $b, $c, $d];
    }
}
