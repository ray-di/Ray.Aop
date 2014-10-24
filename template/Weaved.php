<?php
/**
 * Weaved code-gen template
 *
 * Compiler takes only the statements in the method. Then create new inherit code with interceptors.
 *
 * @see http://paul-m-jones.com/archives/182
 * @see http://stackoverflow.com/questions/8343399/calling-a-function-with-explicit-parameters-vs-call-user-func-array
 * @see http://stackoverflow.com/questions/1796100/what-is-faster-many-ifs-or-else-if
 * @see http://stackoverflow.com/questions/2401478/why-is-faster-than-in-php
 *
 */
class Weaved extends \Ray\Aop\FakeMock
{
    /**
     * @var bool
     */
    private $rayAopIntercept = true;

    /**
     * @var Bind
     */
    public $rayAopBind;

    /**
     * Method Template
     */
    public function returnSame($a)
    {
        if (isset($this->rayAopBind->bindings[__FUNCTION__]) === false){
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        if ($this->rayAopIntercept === false) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        $this->rayAopIntercept = false;
        $invocationResult = (new \Ray\Aop\ReflectiveMethodInvocation(
            [$this, __FUNCTION__],
            func_get_args(),
            $this->rayAopBind->bindings[__FUNCTION__]
        ))->proceed();
        $this->rayAopIntercept = true;

        return $invocationResult;
    }
}
