<?php
namespace Ray\Aop;

abstract class AbstractWeave implements WeavedInterface
{
    public $methodAnnotations;
    public $classAnnotations;
}
