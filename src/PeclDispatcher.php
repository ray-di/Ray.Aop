<?php

declare(strict_types=1);

namespace Ray\Aop;

use Override;
use Ray\Aop\Exception\LogicException;

/**
 * @psalm-import-type ClassBoundInterceptors from Types
 * @psalm-import-type MethodInterceptors from Types
 */
final class PeclDispatcher implements MethodInterceptorInterface
{
    /** @param ClassBoundInterceptors $interceptors */
    public function __construct(private array $interceptors)
    {
    }

    /**
     * @inheritDoc
     * @psalm-suppress MethodSignatureMismatch
     * @psalm-suppress TypeDoesNotContainType
     * @psalm-suppress MixedArgumentTypeCoercion
     * @psalm-suppress ArgumentTypeCoercion
     *
     * (Psalm seems to have a problem with the signature of this method.)
     */
    #[Override]
    public function intercept(object $object, string $method, array $params): mixed
    {
        $class = $object::class;
        if (! isset($this->interceptors[$class][$method])) {
            throw new LogicException('Interceptors not found');
        }

        /** @var MethodInterceptors $interceptors */
        $interceptors = $this->interceptors[$class][$method];

        /** @phpstan-ignore-next-line argument.type */
        $invocation = new ReflectiveMethodInvocation($object, $method, $params, $interceptors);

        return $invocation->proceed();
    }
}
