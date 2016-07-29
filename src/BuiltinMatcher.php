<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

class BuiltinMatcher extends AbstractMatcher
{
    /**
     * @var string
     */
    private $matcherName;

    /**
     * @var AbstractMatcher
     */
    private $matcher;

    /**
     * @param string $matcherName
     * @param array  $arguments
     */
    public function __construct($matcherName, array $arguments)
    {
        parent::__construct();
        $this->matcherName = $matcherName;
        $this->arguments = $arguments;
        $matcher = 'Ray\Aop\Matcher\\' . ucwords($this->matcherName) . 'Matcher';
        $this->matcher = (new \ReflectionClass($matcher))->newInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        return $this->matcher->matchesClass($class, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        return $this->matcher->matchesMethod($method, $arguments);
    }
}
