<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

final class BuiltInMatcher extends AbstractMatcher
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
     * @var arrays
     */
    protected $arguments;

    /**
     * @param string $matcherName
     * @param array  $arguments
     */
    public function __construct($matcherName, array $arguments)
    {
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
