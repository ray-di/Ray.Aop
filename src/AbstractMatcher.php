<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

abstract class AbstractMatcher
{
    abstract public function matchesClass(\ReflectionClass $class, array $arguments);

    abstract public function matchesMethod(\ReflectionMethod $method, array $arguments);
}
