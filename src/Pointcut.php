<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Pointcut
 */
final class Pointcut
{
    /**
     * Class matcher
     *
     * @var AbstractMatcher
     */
    public $classMatcher;

    /**
     * Method matcher
     *
     * @var AbstractMatcher
     */
    public $methodMatcher;

    /**
     * Interceptors
     *
     * @var Interceptor[]
     */
    public $interceptors = [];

    /**
     * @param AbstractMatcher $classMatcher
     * @param AbstractMatcher $methodMatcher
     * @param array           $interceptors
     */
    public function __construct(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
