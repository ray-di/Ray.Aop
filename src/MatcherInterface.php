<?php

declare(strict_types=1);

namespace Ray\Aop;

interface MatcherInterface
{
    /**
     * Returns a matcher which matches any input.
     *
     * @return AbstractMatcher
     */
    public function any();

    /**
     * Returns a matcher which matches elements (methods or classes) with a given annotation.
     *
     * @param string $annotationName
     *
     * @return AbstractMatcher
     */
    public function annotatedWith($annotationName);

    /**
     * Returns a matcher which matches subclasses of the given type (as well as the given type).
     *
     * @param string $superClass
     *
     * @return AbstractMatcher
     */
    public function subclassesOf($superClass);

    /**
     * Returns a matcher which matches if the examined name starts with the specified string.
     *
     * @param string $prefix
     *
     * @return AbstractMatcher
     */
    public function startsWith($prefix);

    /**
     * Returns a matcher which matches if combining two matchers using a logical OR.
     *
     * @return AbstractMatcher
     */
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB);

    /**
     * Returns a matcher which matches if combining two matchers using a logical AND.
     *
     * @return AbstractMatcher
     */
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB);

    /**
     * Returns a matcher which does NOT matches.
     *
     * @return AbstractMatcher
     */
    public function logicalNot(AbstractMatcher $matcher);
}
