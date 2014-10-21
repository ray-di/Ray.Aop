<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Supports matching classes and methods
 */
interface Matchable
{
    /**
     * Any match
     *
     * @return AbstractMatcher
     */
    public function any();

    /**
     * Match binding annotation
     *
     * @param $annotationName
     *
     * @return AbstractMatcher
     */
    public function annotatedWith($annotationName);

    /**
     * Return subclass matched result
     *
     * @param string $superClass
     *
     * @return AbstractMatcher
     */
    public function subclassesOf($superClass);

    /**
     * Return prefix match result
     *
     * @param string $prefix
     *
     * @return AbstractMatcher
     */
    public function startsWith($prefix);

    /**
     * Match logical or
     *
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return Matcher
     */
    public function logicalOr(Matchable $matcherA, Matchable $matcherB);

    /**
     * Match logical and
     *
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return Matcher
     */
    public function logicalAnd(Matchable $matcherA, Matchable $matcherB);


    /**
     * Match logical xor
     *
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return Matcher
     */
    public function logicalXor(Matchable $matcherA, Matchable $matcherB);

    /**
     * Match logical not
     *
     * @param Matchable $matcher
     *
     * @return Matcher
     */
    public function logicalNot(Matchable $matcher);

    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target);
}
