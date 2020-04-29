<?php

declare(strict_types=1);

namespace Ray\Aop;

abstract class AbstractMatcher
{
    /**
     * @var mixed[]
     */
    protected $arguments = [];

    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    /**
     * Match class condition
     *
     * @param \ReflectionClass<object> $class
     * @param mixed[]                  $arguments
     *
     * @return bool
     */
    abstract public function matchesClass(\ReflectionClass $class, array $arguments);

    /**
     * Match method condition
     *
     * @param mixed[] $arguments
     *
     * @return bool
     */
    abstract public function matchesMethod(\ReflectionMethod $method, array $arguments);

    /**
     * Return matching condition arguments
     *
     * @return mixed[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
