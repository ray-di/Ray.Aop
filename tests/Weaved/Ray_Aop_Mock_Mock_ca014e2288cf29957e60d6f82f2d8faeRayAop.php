<?php 
/** doc comment of Mock */
class Ray_Aop_Mock_Mock_ca014e2288cf29957e60d6f82f2d8faeRayAop extends Ray\Aop\Mock\Mock implements Ray\Aop\WeavedInterface
{
    private $rayAopIntercept = true;
    public $rayAopBind;
    /** doc comment of returnSame */
    public function returnSame($a)
    {
        if (!isset($this->rayAopBind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        if (!$this->rayAopIntercept) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        $this->rayAopIntercept = false;
        $interceptors = $this->rayAopBind[__FUNCTION__];
        $annotation = isset($this->rayAopBind->annotation[__FUNCTION__]) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
        return $invocation->proceed();
    }
    /** doc comment of getSub */
    public function getSub($a, $b)
    {
        if (!isset($this->rayAopBind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        if (!$this->rayAopIntercept) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        $this->rayAopIntercept = false;
        $interceptors = $this->rayAopBind[__FUNCTION__];
        $annotation = isset($this->rayAopBind->annotation[__FUNCTION__]) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
        return $invocation->proceed();
    }
    public function returnValue(Ray\Aop\Mock\Num $num = null)
    {
        if (!isset($this->rayAopBind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        if (!$this->rayAopIntercept) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        $this->rayAopIntercept = false;
        $interceptors = $this->rayAopBind[__FUNCTION__];
        $annotation = isset($this->rayAopBind->annotation[__FUNCTION__]) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
        return $invocation->proceed();
    }
    public function getPrivateVal()
    {
        if (!isset($this->rayAopBind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        if (!$this->rayAopIntercept) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }
        $this->rayAopIntercept = false;
        $interceptors = $this->rayAopBind[__FUNCTION__];
        $annotation = isset($this->rayAopBind->annotation[__FUNCTION__]) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
        return $invocation->proceed();
    }
}