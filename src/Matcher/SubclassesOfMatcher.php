<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Exception\InvalidMatcher;

class SubclassesOfMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($superClass) = $arguments;
        $isSubClass = $class->isSubclassOf($superClass) || ($class->name === $superClass);

        return $isSubClass;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        throw new InvalidMatcher('subclassesOf is only for class match');
    }
}
