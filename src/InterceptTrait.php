<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

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

    /** @var bool Flag controlling whether aspect interception is active */
    private $_isAspect = true;

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

    /**
     * @param MethodName           $func
     * @param ConstructorArguments $args
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _intercept(string $func, array $args) // phpcs:ignore
    {
        if (! $this->_isAspect) {
            $this->_isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->_isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->bindings[$func]))->proceed();
        $this->_isAspect = true;

        return $result;
    }
}
