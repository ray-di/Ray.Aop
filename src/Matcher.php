<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;

class Matcher implements MatcherInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function any()
    {
        return new BuiltinMatcher(__FUNCTION__, []);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function annotatedWith($annotationName) : AbstractMatcher
    {
        if (! class_exists($annotationName)) {
            throw new InvalidAnnotationException($annotationName);
        }

        return new AnnotatedMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function subclassesOf($superClass) : AbstractMatcher
    {
        if (! class_exists($superClass)) {
            throw new InvalidArgumentException($superClass);
        }

        return new BuiltinMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function startsWith($prefix) : AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, [$prefix]);
    }

    // @codingStandardsIgnoreStart

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB) : AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB) : AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, func_get_args());
    }

    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     */
    public function logicalNot(AbstractMatcher $matcher) : AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, [$matcher]);
    }
}
