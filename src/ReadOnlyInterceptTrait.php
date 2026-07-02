<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * @psalm-import-type MethodBindings from Types
 * @psalm-import-type Arguments from Types
 * @phpstan-ignore trait.unused
 */
trait ReadOnlyInterceptTrait
{
    private readonly InterceptTraitState $_state;

    /**
     * @param MethodBindings $bindings
     *
     * @see WeavedInterface::_setBindings()
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _setBindings(array $bindings): void // @phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->_state = new InterceptTraitState($bindings);
    }
}
