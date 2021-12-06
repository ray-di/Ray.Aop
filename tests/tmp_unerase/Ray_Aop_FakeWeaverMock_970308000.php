<?php

declare (strict_types=1);
namespace Ray\Aop;

use Ray\Aop\WeavedInterface;
use Ray\Aop\ReflectiveMethodInvocation as Invocation;
/** doc comment of FakeMock */
class FakeWeaverMock_970308000 extends \Ray\Aop\FakeWeaverMock implements WeavedInterface
{
    public $bind;
    public $bindings = [];
    public $methodAnnotations = 'a:0:{}';
    public $classAnnotations = 'a:0:{}';
    private $isAspect = true;
    /**
     * doc comment of returnSame
     */
    public function returnSame($a)
    {
        if (!$this->isAspect) {
            $this->isAspect = true;
            call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
            return;
        }
        $this->isAspect = false;
        (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
    }
    /**
     * doc comment of getSub
     */
    public function getSub($a, $b)
    {
        if (!$this->isAspect) {
            $this->isAspect = true;
            call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
            return;
        }
        $this->isAspect = false;
        (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
    }
    public function returnValue(?FakeNum $num = null)
    {
        if (!$this->isAspect) {
            $this->isAspect = true;
            call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
            return;
        }
        $this->isAspect = false;
        (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
    }
    public function getPrivateVal()
    {
        if (!$this->isAspect) {
            $this->isAspect = true;
            call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
            return;
        }
        $this->isAspect = false;
        (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
    }
}
