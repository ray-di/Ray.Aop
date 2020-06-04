<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\AbstractMatcher;

final class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        [$contains] = $arguments;

        return strpos($class->name, $contains) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        [$contains] = $arguments;

        return strpos($method->name, $contains) !== false;
    }
}
