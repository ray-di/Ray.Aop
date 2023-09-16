<?php

declare (strict_types=1);
namespace Ray\Aop;

/** doc comment of FakeMock */
class FakeWeaverMock_950495179 extends \Ray\Aop\FakeWeaverMock implements \Ray\Aop\WeavedInterface
{
    use \Ray\Aop\InterceptTrait;
    
    /**
     * doc comment of returnSame
     */
    public function returnSame($a)
    {
        return $this->_intercept(func_get_args(), __FUNCTION__);
    }
    
    /**
     * doc comment of getSub
     */
    public function getSub($a, $b)
    {
        return $this->_intercept(func_get_args(), __FUNCTION__);
    }
    
    public function returnValue(?\Ray\Aop\FakeNum $num = null)
    {
        return $this->_intercept(func_get_args(), __FUNCTION__);
    }
    
    public function getPrivateVal()
    {
        return $this->_intercept(func_get_args(), __FUNCTION__);
    }
}
