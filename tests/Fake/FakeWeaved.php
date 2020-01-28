<?php
namespace Ray\Aop;

class FakeWeaved extends FakeMock
{
    private $rayAopIntercept = true;

    private $bind;

    public function ___postConstruct(Bind $bind)
    {
        $this->bind = $bind;
    }

    public function returnSame($a)
    {
        unset($a);
        // direct call
        if (! $this->rayAopIntercept || ! isset($this->bind[__FUNCTION__])) {
            $callable = 'parent::' . __FUNCTION__;
            assert(is_callable($callable));
            return call_user_func_array($callable, func_get_args());
        }

        $this->rayAopIntercept = true;

        // interceptor weaved call
        $interceptors = $this->bind[__FUNCTION__];
        $invocation = new ReflectiveMethodInvocation(
            $this,
            new \ReflectionMethod($this, __FUNCTION__),
            func_get_args(),
            $interceptors
        );

        return $invocation->proceed();
    }
}
