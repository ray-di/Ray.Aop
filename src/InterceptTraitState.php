<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
final class InterceptTraitState
{
    /**
     * @var bool
     * @deprecated No longer used since _intercept() was removed. Will be removed in 3.0.
     */
    public $isAspect = true;

    /** @param MethodBindings $bindings */
    public function __construct(
        public readonly array $bindings,
    ) {
    }
}
