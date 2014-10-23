<?php
// @codingStandardsIgnoreStart
// @codeCoverageIgnoreStart

/**
 * Weaved class template
 *
 *  - Compiler takes only the statements code method inside to create new subclass PHP code.
 *
 * @see http://paul-m-jones.com/archives/182
 * @see http://stackoverflow.com/questions/8343399/calling-a-function-with-explicit-parameters-vs-call-user-func-array
 * @see http://stackoverflow.com/questions/1796100/what-is-faster-many-ifs-or-else-if
 * @see http://stackoverflow.com/questions/2401478/why-is-faster-than-in-php
 *
 */
class Weaved extends \Ray\Aop\Mock\Mock
{
    private $rayAopIntercept = true;
    public $rayAopBind;

    public function returnSame($a)
    {
        if (isset($this->rayAopBind[__FUNCTION__]) === false){
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        if ($this->rayAopIntercept === false) {
            $this->rayAopIntercept = true;
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        $this->rayAopIntercept = false;
        $invocationResult = (new \Ray\Aop\ReflectiveMethodInvocation(
            [$this,__FUNCTION__],
            func_get_args(),
            $this->rayAopBind[__FUNCTION__],
            (isset($this->rayAopBind->annotation[__FUNCTION__])) ? $this->rayAopBind->annotation[__FUNCTION__] : null
        ))->proceed();
        $this->rayAopIntercept = true;

        return $invocationResult;
    }
}
// @codeCoverageIgnoreEnd
// @codingStandardsIgnoreEnd
