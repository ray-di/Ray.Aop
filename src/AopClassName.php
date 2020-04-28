<?php

declare(strict_types=1);

namespace Ray\Aop;

class AopClassName
{
    /**
     * @param class-string $class
     */
    public function __invoke(string $class, string $bindName) : string
    {
        return sprintf('%s_%s', $class, $bindName);
    }
}
