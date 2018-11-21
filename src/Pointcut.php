<?php

declare(strict_types=1);

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
     * @param MethodInterceptor[] $interceptors
     */
    public function __construct(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
