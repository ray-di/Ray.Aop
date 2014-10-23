<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;

class Matcher extends AbstractMatcher implements Matchable
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
    public function logicalOr(Matchable $matcherA, Matchable $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(Matchable $matcherA, Matchable $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalXor(Matchable $matcherA, Matchable $matcherB)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(Matchable $matcher)
    {
        return new self(__FUNCTION__, func_get_args());
    }

    /**
     * Return isAnnotateBinding
     *
     * @return bool
     */
    public function isAnnotateBinding()
    {
        return $this->method === 'annotatedWith';
    }
}
