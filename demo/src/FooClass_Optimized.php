<?php

declare(strict_types=1);

use Ray\Aop\ReflectiveMethodInvocation as Invocation;

class Ray_Aop_Demo_Optimized extends Ray\Aop\Demo\FooClass implements Ray\Aop\WeavedInterface
{
    public $bind;
    public $bindings = [];
    public $methodAnnotations = 'a:0:{}';
    public $classAnnotations = 'a:0:{}';
    private $isAspect = true;

    public function intercepted()
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
