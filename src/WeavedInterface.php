<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
interface WeavedInterface
{
    /**
     * @param MethodBindings $bindings
     *
     * @SuppressWarnings("PHPMD.CamelCaseMethodName")
     */
    public function _setBindings(array $bindings): void; // phpcs:ignore
}
