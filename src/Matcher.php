<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;
use Ray\Aop\Exception\InvalidArgumentException;

class Matcher
{
    /**
     * {@inheritdoc}
     */
    public function any()
    {
        return new BuiltInMatcher(__FUNCTION__, []);
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedWith($annotationName)
    {
        if (! class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }

        return new BuiltInMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        if (! class_exists($superClass)) {
            throw new InvalidArgumentException($superClass);
        }
        return new BuiltInMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        if (! is_string($prefix)) {
            throw new InvalidArgumentException($prefix);
        }
        return new BuiltInMatcher(__FUNCTION__, [$prefix]);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
    {
        return new BuiltInMatcher(__FUNCTION__, [$matcherA, $matcherB]);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
    {
        return new BuiltInMatcher(__FUNCTION__, [$matcherA, $matcherB]);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(AbstractMatcher $matcher)
    {
        return new BuiltInMatcher(__FUNCTION__, [$matcher]);
    }
}
