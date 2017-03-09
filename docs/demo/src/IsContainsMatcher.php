<?php
namespace Ray\Aop\Demo;

use Ray\Aop\AbstractMatcher;

class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($contains) = $arguments;

        return strpos($class->name, $contains) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        list($contains) = $arguments;

        return strpos($method->name, $contains) !== false;
    }
}
