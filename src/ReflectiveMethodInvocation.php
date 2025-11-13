<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayObject;
use Override;
use ReflectionClass;
use ReflectionObject;

use function assert;
use function call_user_func_array;
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
    /**
     * @var ArgumentList
     * @readonly
     */
    private $arguments;

    /**
     * @var callable(mixed...): mixed
     * @readonly
     */
    private $callable;
    private int $currentInterceptorIndex = 0;

    /**
     * @param T                    $object       Target object
     * @param MethodName           $method       Method name
     * @param ConstructorArguments $arguments    Method arguments
     * @param InterceptorList      $interceptors Method interceptors
     */
    public function __construct(
        /** @readonly */
        private readonly object $object,
        /** @readonly */
        private readonly string $method,
        array $arguments,
        /** @readonly */
        private readonly array $interceptors = [],
    ) {
        $callable = [$this->object, $this->method];
        assert(is_callable($callable));
        $this->callable = $callable;
        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->arguments = new ArrayObject($arguments);
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
     * @psalm-mutation-free
     */
    #[Override]
    public function getArguments(): ArrayObject
    {
        return $this->arguments;
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

        return call_user_func_array($this->callable, (array) $this->arguments);
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
