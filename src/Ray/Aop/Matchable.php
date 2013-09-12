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
     * @return Matcher
     */
    public function any();

    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return array
     */
    public function annotatedWith($annotationName);

    /**
     * Return subclass matched result
     *
     * @param string $superClass
     *
     * @return bool
     */
    public function subclassesOf($superClass);

    /**
     * Return prefix match result
     *
     * @param string $prefix
     *
     * @return Matcher
     */
    public function startWith($prefix);

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
