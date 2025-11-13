<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type PointcutInterceptors from Types */
readonly class Pointcut
{
    /** @param PointcutInterceptors $interceptors */
    public function __construct(
        public AbstractMatcher $classMatcher,
        public AbstractMatcher $methodMatcher,
        public array $interceptors,
    ) {
    }
}
