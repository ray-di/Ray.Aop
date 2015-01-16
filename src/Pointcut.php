<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

class Pointcut
{
    /**
     * @var AbstractMatcher
     */
    public $classMatcher;

    /**
     * @var AbstractMatcher
     */
    public $methodMatcher;

    /**
     * @var Interceptor[]
     */
    public $interceptors = [];

    /**
     * @param AbstractMatcher $classMatcher
     * @param AbstractMatcher $methodMatcher
     * @param Interceptor[]   $interceptors
     */
    public function __construct(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
