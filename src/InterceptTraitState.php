<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
final class InterceptTraitState
{
    /** @param MethodBindings $bindings */
    public function __construct(
        public readonly array $bindings,
    ) {
    }
}
