<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * Aop code template
 *
 * $bindings @var array [$methodName => [$interceptorA[]][]
 * $isAspect @var bool
 */
final class AopTemplate
{
    /**
     * Return void aop code
     */
    public const RETURN_VOID = /* @lang PHP */ <<<'EOT'
<?php
class AopTemplate extends \Ray\Aop\FakeMock implements Ray\Aop\WeavedInterface
{
    public $bindings;
    private $isAspect = true;
    public function templateMethod($a, $b)
    {
        $this->_intercept(func_get_args(), __FUNCTION__);
    }
}
EOT;

    /**
     * Return (mixed) aop code
     */
    public const RETURN = /* @lang PHP */ <<<'EOT'
<?php
class AopTemplate extends \Ray\Aop\FakeMock implements Ray\Aop\WeavedInterface
{
    public $bindings;
    private $isAspect = true;
    public function templateMethod($a, $b)
    {
        return $this->_intercept(func_get_args(), __FUNCTION__);
    }
}
EOT;
}
