<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

/** @psalm-import-type MethodBindings from Types */
trait InterceptTrait
{
    /**
     * @var InterceptTraitState
     * @readonly
     * @psalm-suppress MissingConstructor
     */
    private $_state;

    /**
     * @param MethodBindings $bindings
     *
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initState(array $bindings): void // phpcs:ignore
    {
        $this->_state = new InterceptTraitState($bindings);
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _intercept(string $func, array $args) // phpcs:ignore
    {
        if (! $this->_state->isAspect) {
            $this->_state->isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->_state->isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->_state->bindings[$func]))->proceed();
        $this->_state->isAspect = true;

        return $result;
    }
}
