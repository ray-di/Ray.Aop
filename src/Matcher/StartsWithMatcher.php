<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

class StartsWithMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($startsWith) = $arguments;

        return (strpos($class->name, $startsWith) === 0);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        list($startsWith) = $arguments;

        return (strpos($method->name, $startsWith) === 0);
    }
}
