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
    const RETURN_VOID = /* @lang PHP */ <<<'EOT'
<?php
class AopTemplate extends \Ray\Aop\FakeMock implements Ray\Aop\WeavedInterface
{
    public $bindings;
    private $isAspect = true;
    public function templateMethod($a, $b)
    {
        if (! $this->isAspect) {
            $this->isAspect = true;
            call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());

            return; 
        }
        $this->isAspect = false;
        (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
    }
}
EOT;

    /**
     * Return (mixed) aop code
     */
    const RETURN = /* @lang PHP */ <<<'EOT'
<?php
class AopTemplate extends \Ray\Aop\FakeMock implements Ray\Aop\WeavedInterface
{
    public $bindings;
    private $isAspect = true;
    public function templateMethod($a, $b)
    {
        if (! $this->isAspect) {
            $this->isAspect = true;

            return call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
        }
        $this->isAspect = false;
        $result = (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
        
        return $result;
    }
}
EOT;
}
