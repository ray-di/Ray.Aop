<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
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
     * @var MethodInterceptor[]
     */
    public $interceptors = [];

    /**
     * @param AbstractMatcher     $classMatcher
     * @param AbstractMatcher     $methodMatcher
     * @param MethodInterceptor[] $interceptors
     */
    public function __construct(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
