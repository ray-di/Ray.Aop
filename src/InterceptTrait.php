<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * @psalm-import-type MethodBindings from Types
 * @psalm-import-type MethodName from Types
 * @psalm-import-type ConstructorArguments from Types
 */
trait InterceptTrait // @phpstan-ignore-line
{
    /**
     * @var MethodBindings
     * @readonly
     * @deprecated Do not use this property directly. Use the `_setBindings` setter method instead for initialization.
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
