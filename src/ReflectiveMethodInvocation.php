<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayObject;
use Closure;
use Override;
use ReflectionClass;
use ReflectionObject;

use function assert;
use function is_callable;

/**
 * @psalm-import-type ArgumentList from Types
 * @psalm-import-type NamedArguments from Types
 * @psalm-import-type InterceptorList from Types
 * @psalm-import-type MethodName from Types
 * @psalm-import-type ConstructorArguments from Types
 * @template T of object
 * @implements MethodInvocation<T>
 */
final class ReflectiveMethodInvocation implements MethodInvocation
{
    /** @var list<mixed> Plain array for fast access in proceed() */
    private readonly array $arguments;

    /** @var ArgumentList|null Lazy-created only if getArguments() is called */
    private ArrayObject|null $argumentsObject = null;

    /** @var callable(mixed...): mixed Pre-bound callable for fast dispatch */
    private readonly mixed $callable;
    private int $currentInterceptorIndex = 0;

    /**
     * @param T                    $object       Target object
     * @param MethodName           $method       Method name
     * @param ConstructorArguments $arguments    Method arguments
     * @param InterceptorList      $interceptors Method interceptors
     * @param Closure|null         $parentCall   Direct parent-method closure (avoids double-dispatch through proxy)
     * @psalm-param (Closure(mixed...): mixed)|null $parentCall
     * @phpstan-param (Closure(mixed...): mixed)|null $parentCall
     */
    public function __construct(
        /** @readonly */
        private readonly object $object,
        /** @readonly */
        private readonly string $method,
        array $arguments,
        /** @readonly */
        private readonly array $interceptors = [],
        Closure|null $parentCall = null,
    ) {
        if ($parentCall !== null) {
            $this->callable = $parentCall;
        } else {
            $callable = [$this->object, $this->method];
            assert(is_callable($callable));
            $this->callable = $callable;
        }

        $this->arguments = $arguments;
    }

    #[Override]
    public function getMethod(): ReflectionMethod
    {
        if ($this->object instanceof WeavedInterface) {
            $class = (new ReflectionObject($this->object))->getParentClass();
            assert($class instanceof ReflectionClass);

            return new ReflectionMethod($class->name, $this->method);
        }

        return new ReflectionMethod($this->object, $this->method);
    }

    /**
     * {@inheritDoc}
     *
     * @return ArgumentList
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getArguments(): ArrayObject
    {
        if ($this->argumentsObject === null) {
            /** @var ArgumentList $argumentsObject */
            $argumentsObject = new ArrayObject($this->arguments);
            $this->argumentsObject = $argumentsObject;
        }

        return $this->argumentsObject;
    }

    /**
     * {@inheritDoc}
     *
     * @return NamedArguments
     */
    #[Override]
    public function getNamedArguments(): ArrayObject
    {
        $args = $this->getArguments();
        $params = $this->getMethod()->getParameters();
        $namedParams = [];
        foreach ($params as $param) {
            $pos = $param->getPosition();
            $name = $param->getName();
            /** @psalm-suppress MixedAssignment */
            $namedParams[$name] = $args[$pos];
        }

        return new ArrayObject($namedParams); // @phpstan-ignore-line
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function proceed()
    {
        if (isset($this->interceptors[$this->currentInterceptorIndex])) {
            $interceptor = $this->interceptors[$this->currentInterceptorIndex++];

            return $interceptor->invoke($this);
        }

        // Use ArrayObject if getArguments() was called (and possibly mutated),
        // otherwise use the fast plain array path
        if ($this->argumentsObject !== null) {
            return ($this->callable)(...$this->argumentsObject->getArrayCopy());
        }

        return ($this->callable)(...$this->arguments);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function getThis()
    {
        return $this->object;
    }
}
