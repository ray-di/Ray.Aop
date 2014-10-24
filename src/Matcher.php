<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;

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
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }

        return new BuiltInMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        return new BuiltInMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
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
    public function logicalXor(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
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
