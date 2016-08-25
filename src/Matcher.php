<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;

class Matcher implements MatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function any()
    {
        return new BuiltinMatcher(__FUNCTION__, []);
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedWith($annotationName)
    {
        if (! class_exists($annotationName)) {
            throw new InvalidAnnotationException($annotationName);
        }

        return new AnnotatedMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        if (! class_exists($superClass)) {
            throw new InvalidArgumentException($superClass);
        }

        return new BuiltinMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        if (! is_string($prefix)) {
            throw new InvalidArgumentException($prefix);
        }

        return new BuiltinMatcher(__FUNCTION__, [$prefix]);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB, AbstractMatcher ...$otherMatchers)
    {
        return new BuiltinMatcher(__FUNCTION__, [$matcherA, $matcherB] + $otherMatchers);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB, AbstractMatcher ...$otherMatchers)
    {
        return new BuiltinMatcher(__FUNCTION__, [$matcherA, $matcherB] + $otherMatchers);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(AbstractMatcher $matcher)
    {
        return new BuiltinMatcher(__FUNCTION__, [$matcher]);
    }
}
