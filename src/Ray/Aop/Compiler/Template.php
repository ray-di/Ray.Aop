<?php

/**
 * Weaved class template
 *
 *  - Compiler takes only the statements code method inside to create new subclass PHP code.
 *
 * @see http://paul-m-jones.com/archives/182
 * @see http://stackoverflow.com/questions/8343399/calling-a-function-with-explicit-parameters-vs-call-user-func-array
 * @see http://stackoverflow.com/questions/1796100/what-is-faster-many-ifs-or-else-if
 * @see http://stackoverflow.com/questions/2401478/why-is-faster-than-in-php
 */
class Weaved extends \Ray\Aop\Mock\Mock
{
    private $rayAopIntercept = true;
    public $rayAopBind;

    public function returnSame($a)
    {
        // native call
        if (! isset($this->rayAopBind[__FUNCTION__])){
            // @codeCoverageIgnoreStart
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
            // @codeCoverageIgnoreEnd
        }

        // proceed source method from interceptor
        if (! $this->rayAopIntercept) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        // proceed next interceptor
        $this->rayAopIntercept = false;
        $interceptors = $this->rayAopBind[__FUNCTION__];
        $annotation = (isset($this->rayAopBind->annotation[__FUNCTION__])) ? $this->rayAopBind->annotation[__FUNCTION__] : null;
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation([$this,__FUNCTION__], func_get_args(), $interceptors, $annotation);

        return $invocation->proceed();
    }
}
