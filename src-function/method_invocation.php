<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
function method_invocation(array $bindngs, array $args)
{
    if (isset($bindngs[__FUNCTION__]) === false){
        return call_user_func_array('parent::' . __FUNCTION__, $args);
    }

    if ($this->intercept === false) {
        $this->intercept = true;
        return call_user_func_array('parent::' . __FUNCTION__, $args);
    }

    $this->intercept = false;
    $invocationResult = (new \Ray\Aop\ReflectiveMethodInvocation(
        $this,
        new \ReflectionMethod($this, __FUNCTION__),
        new \Ray\Aop\Arguments(func_get_args()),
        $this->bind->bindings[__FUNCTION__]
    ))->proceed();
    $this->intercept = true;

    return $invocationResult;

}
