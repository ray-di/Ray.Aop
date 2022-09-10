<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

use function call_user_func_array;

trait AopTrait
{
    /** @var array<string, string> */
    private $bind;

    /** @var array<string, string> */
    public $bindings = [];

    /** @var bool */
    private $isAspect = true;

    /**
     * @param array<string, mixed> $args
     *
     * @return mixed
     */
    private function __aop(array $args, string $func)
    {
        if (! $this->isAspect) {
            $this->isAspect = true;

            return call_user_func_array([parent::class, $func], $args);
        }

        $this->isAspect = false;
        $result = (new Invocation($this, $func, $args, $this->bindings[$func]))->proceed();
        $this->isAspect = true;

        return $result;
    }
}
