<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Ray_Aop_Demo_Optimized extends Ray\Aop\Demo\FooClass implements Ray\Aop\WeavedInterface
{
    public $bind;
    public $methodAnnotations = 'a:0:{}';
    public $classAnnotations = 'a:0:{}';
    private $isIntercepting = true;

    public function intercepted()
    {
        if (isset($this->bindings[__FUNCTION__]) === false) {
            // call original method - no biding
            // return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
            return parent::intercepted();
        }
        if ($this->isIntercepting === false) {
            $this->isIntercepting = true;
            // call original method
            // return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
            return parent::intercepted();
        }
        $this->isIntercepting = false;
        // call interceptor
        $result = (new \Ray\Aop\ReflectiveMethodInvocation($this, new \ReflectionMethod($this, __FUNCTION__), new \Ray\Aop\Arguments(func_get_args()), $this->bindings[__FUNCTION__]))->proceed();
        $this->isIntercepting = true;

        return $result;
    }
}
