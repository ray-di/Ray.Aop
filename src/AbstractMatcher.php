<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

abstract class AbstractMatcher
{
    /**
     * @var array
     */
    protected $arguments;

    abstract public function matchesClass(\ReflectionClass $class, array $arguments);

    abstract public function matchesMethod(\ReflectionMethod $method, array $arguments);

    /**
     * Return matching condition arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
