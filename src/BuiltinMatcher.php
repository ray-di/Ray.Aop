<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidMatcherException;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function class_exists;
use function ucwords;

class BuiltinMatcher extends AbstractMatcher
{
    /** @var string */
    private $matcherName;

    /** @var AbstractMatcher */
    private $matcher;

    /**
     * @param mixed[] $arguments
     */
    public function __construct(string $matcherName, array $arguments)
    {
        parent::__construct();
        $this->matcherName = $matcherName;
        $this->arguments = $arguments;
        $matcherClass = 'Ray\Aop\Matcher\\' . ucwords($this->matcherName) . 'Matcher';
        assert(class_exists($matcherClass));
        $matcher = (new ReflectionClass($matcherClass))->newInstance();
        if (! $matcher instanceof AbstractMatcher) {
            throw new InvalidMatcherException($matcherClass);
        }

        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        return $this->matcher->matchesClass($class, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        return $this->matcher->matchesMethod($method, $arguments);
    }
}
