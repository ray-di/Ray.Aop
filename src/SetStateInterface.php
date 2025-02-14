<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\MethodInterceptor as MethodBindings;

/** @psalm-import-type MethodBindings from Types */
interface SetStateInterface extends WeavedInterface
{
    /**
     * @param MethodBindings $bindings
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initState(array $bindings): void; // phpcs:ignore
}
