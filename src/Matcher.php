<?php

declare(strict_types=1);

namespace Ray\Aop;

use Override;
use Ray\Aop\Exception\InvalidAnnotationException;
use Ray\Aop\Exception\InvalidArgumentException;

use function class_exists;
use function func_get_args;

final class Matcher implements MatcherInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function any()
    {
        return new BuiltinMatcher(__FUNCTION__, []);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function annotatedWith($annotationName): AbstractMatcher
    {
        if (! class_exists($annotationName)) {
            throw new InvalidAnnotationException($annotationName);
        }

        return new AnnotatedMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function subclassesOf($superClass): AbstractMatcher
    {
        if (! class_exists($superClass)) {
            throw new InvalidArgumentException($superClass);
        }

        return new BuiltinMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function startsWith($prefix): AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, [$prefix]);
    }

    // @codingStandardsIgnoreStart

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB): AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB): AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, func_get_args());
    }

    // @codingStandardsIgnoreEnd

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function logicalNot(AbstractMatcher $matcher): AbstractMatcher
    {
        return new BuiltinMatcher(__FUNCTION__, [$matcher]);
    }
}
