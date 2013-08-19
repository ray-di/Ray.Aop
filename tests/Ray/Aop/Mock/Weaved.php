<?php

namespace Ray\Aop\Mock;


/**
 * Test class for Ray.Aop
 */
class Weaved extends MockMethod
{
    private $___initialized = false;

    public function ___postConstruct(\Ray\Aop\Bind $bind)
    {
        $this->bind = $bind;
    }

    public function returnSame($a)
    {
        // direct call
        if ($this->___initialized || !isset($this->bind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        $this->___initialized = true;

        // interceptor weaved call
        $interceptors = $this->bind[__FUNCTION__];
        $annotation = (isset($this->bind->annotation[__FUNCTION__])) ? $this->bind->annotation[__FUNCTION__] : null;
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
}
