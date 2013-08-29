<?php

class Weaved extends \Ray\Aop\Mock\Mock
{
    private $rayAopIntercept = true;
    public $rayAopBind;

    public function returnSame($a)
    {
        if ($this->rayAopIntercept) {
            $this->rayAopIntercept = false;
            $interceptors = $this->rayAopBind[__FUNCTION__];
            $annotation = (isset($this->rayAopBind->annotation[__FUNCTION__])) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
            $invocation = new \Ray\Aop\ReflectiveMethodInvocation(
                [
                    $this,
                    __FUNCTION__
                ],
                func_get_args(),
                $interceptors,
                $annotation
            );

            return $invocation->proceed();
        }
        $this->rayAopIntercept = true;
        return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }
}
