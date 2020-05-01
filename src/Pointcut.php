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
     * @var array<MethodInterceptor|class-string>
     */
    public $interceptors = [];

    /**
     * @param array<MethodInterceptor|class-string> $interceptors
     */
    public function __construct(AbstractMatcher $classMatcher, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
