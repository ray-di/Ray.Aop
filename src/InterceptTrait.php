<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
trait InterceptTrait // @phpstan-ignore-line
{
    /**
     * Interceptor map for this weaved instance.
     * Written once via `_setBindings()`; thereafter treated as readonly and read by generated proxy methods.
     *
     * @var MethodBindings
     * @readonly
     */
    public $bindings = [];

    /**
     * @param MethodBindings $bindings
     *
     * @see WeavedInterface::_setBindings()
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _setBindings(array $bindings): void // @phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->bindings = $bindings;
    }
}
