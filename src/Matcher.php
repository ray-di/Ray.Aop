<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;

class Matcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function any()
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedWith($annotationName)
    {
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }

        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalOr(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalXor(AbstractMatcher $matcherA, AbstractMatcher $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(AbstractMatcher $matcher)
    {
        return new self(__FUNCTION__, func_get_args());
    }
}
