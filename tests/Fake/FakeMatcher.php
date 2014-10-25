<?php

namespace Ray\Aop;

class FakeMatcher extends AbstractMatcher
{
    private $return;

    public function __construct($return = true)
    {
        $this->return = $return;
    }

    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        return $this->return;
    }

    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        return $this->return;
    }
}
