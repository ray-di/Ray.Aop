<?php
class Mock_ray569357a3a82a9de18a16206045ebb680 extends Ray\Aop\Mock\Mock
{
    private $___intercept = true;
    public $___bind;
    public function returnSame($a)
    {
        if ($this->___intercept) {
            $this->___intercept = false;
            $interceptors = $this->___bind[__FUNCTION__];
            $annotation = isset($this->___bind->annotation[__FUNCTION__]) ? $this->___bind->annotation[__FUNCTION__] : null;
            $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
            return $invocation->proceed();
        }
        $this->___intercept = true;
        return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }
    public function returnValue(Ray\Aop\Mock\Num $num = null)
    {
        if ($this->___intercept) {
            $this->___intercept = false;
            $interceptors = $this->___bind[__FUNCTION__];
            $annotation = isset($this->___bind->annotation[__FUNCTION__]) ? $this->___bind->annotation[__FUNCTION__] : null;
            $invocation = new \Ray\Aop\ReflectiveMethodInvocation(array($this, __FUNCTION__), func_get_args(), $interceptors, $annotation);
            return $invocation->proceed();
        }
        $this->___intercept = true;
        return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }
}
