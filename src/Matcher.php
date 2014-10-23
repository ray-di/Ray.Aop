<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidAnnotation;
use Doctrine\Common\Annotations\Reader;

class Matcher extends AbstractMatcher implements Matchable
{
    /**
     * {@inheritdoc}
     */
    public function any()
    {
        return $this->createMatcher(__FUNCTION__, [null]);
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedWith($annotationName)
    {
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }

        return $this->createMatcher(__FUNCTION__, [$annotationName]);
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        return $this->createMatcher(__FUNCTION__, [$superClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        return $this->createMatcher(__FUNCTION__, [$prefix]);
    }

    /**
     * {@inheritdoc}
     */
    public function logicalOr(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalXor(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(Matchable $matcher)
    {
        $this->method = __FUNCTION__;
        $this->args = [$matcher];

        return clone $this;
    }

    /**
     * Return isAnnotateBinding
     *
     * @return bool
     */
    public function isAnnotateBinding()
    {
        $isAnnotateBinding = $this->method === 'annotatedWith';

        return $isAnnotateBinding;
    }

    /**
     * @param Reader $reader
     */
    public static function setAnnotationReader(Reader $reader)
    {
        Match::setAnnotationReader($reader);
    }
}
