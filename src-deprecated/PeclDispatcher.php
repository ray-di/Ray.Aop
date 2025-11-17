<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\LogicException;

/**
 * PECL extension dispatcher (deprecated)
 *
 * @deprecated 2.19.0 PECL extension support is deprecated and will be removed in 3.0.0.
 *                    Use proxy-based AOP with $aspect->bind() + newInstance() instead.
 *                    See https://github.com/ray-di/Ray.Aop/issues/242 for details.
 */
final class PeclDispatcher
{
    /**
     * @param mixed ...$args Accepts any arguments for backward compatibility
     *
     * @throws LogicException Always throws as PECL extension is no longer supported
     * @deprecated 2.19.0 Use proxy-based AOP instead
     */
    public function __construct(mixed ...$args)
    {
        throw new LogicException(
            'PECL extension support has been removed in Ray.Aop 2.19.0. ' .
            'Use proxy-based AOP with $aspect->bind() + newInstance() instead. ' .
            'See https://github.com/ray-di/Ray.Aop/issues/242 for migration guide.'
        );
    }
}
