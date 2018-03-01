<?php 

class Ray_Aop_Demo_AnnotationRealBillingService_NXhGYhg extends Ray\Aop\Demo\AnnotationRealBillingService implements Ray\Aop\WeavedInterface
{
    private $isIntercepting = true;
    public $bind;
    public $methodAnnotations = 'a:1:{s:11:"chargeOrder";a:1:{s:25:"Ray\\Aop\\Demo\\WeekendBlock";O:25:"Ray\\Aop\\Demo\\WeekendBlock":0:{}}}';
    public $classAnnotations = 'a:0:{}';
    /**
     * @WeekendBlock
     */
    function chargeOrder()
    {
        if (isset($this->bindings[__FUNCTION__]) === false) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        if ($this->isIntercepting === false) {
            $this->isIntercepting = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        $this->isIntercepting = false;
        $invocationResult = (new \Ray\Aop\ReflectiveMethodInvocation($this, new \Ray\Aop\ReflectionMethod($this, __FUNCTION__), new \Ray\Aop\Arguments(func_get_args()), $this->bindings[__FUNCTION__]))->proceed();
        $this->isIntercepting = true;
        return $invocationResult;
    }
}
