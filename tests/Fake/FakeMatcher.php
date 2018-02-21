<?php
namespace Ray\Aop;

class FakeMatcher extends AbstractMatcher
{
    public function __construct($arg1 = true, $arg2 = true)
    {
        parent::__construct();
        $this->arguments = [$arg1, $arg2];
    }

    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        unset($class);
        if (isset($arguments[1])) {
            return $arguments[0] && $arguments[1];
        }

        return $arguments[0];
    }

    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        unset($method);
        if (isset($arguments[1])) {
            return $arguments[0] && $arguments[1];
        }

        return $arguments[0];
    }
}
