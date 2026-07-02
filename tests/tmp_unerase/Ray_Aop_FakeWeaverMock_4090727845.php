<?php

declare(strict_types=1);

namespace Ray\Aop;

/** doc comment of FakeMock */
class FakeWeaverMock_4090727845 extends FakeWeaverMock implements \Ray\Aop\WeavedInterface 
{
    use \Ray\Aop\InterceptTrait;
    /**
     * doc comment of returnSame
     */
      public function returnSame($a)
    {
        $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'returnSame', func_get_args(), $this->bindings['returnSame'], parent::returnSame(...));
        return $__aop->proceed();    }

    /**
     * doc comment of getSub
     */
      public function getSub($a, $b)
    {
        $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'getSub', func_get_args(), $this->bindings['getSub'], parent::getSub(...));
        return $__aop->proceed();    }

    public function returnValue(null|\Ray\Aop\FakeNum $num = NULL)
    {
        $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'returnValue', func_get_args(), $this->bindings['returnValue'], parent::returnValue(...));
        return $__aop->proceed();    }

    public function getPrivateVal()
    {
        $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, 'getPrivateVal', func_get_args(), $this->bindings['getPrivateVal'], parent::getPrivateVal(...));
        return $__aop->proceed();    }
}