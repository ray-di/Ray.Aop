<?php

declare(strict_types=1);

namespace Ray\Aop;

/** doc comment of FakeMock */
class FakeWeaverMock_1899453090 extends FakeWeaverMock implements \Ray\Aop\WeavedInterface 
{
    use \Ray\Aop\InterceptTrait;
    /**
     * doc comment of returnSame
     */
      public function returnSame($a)
    {
        return $this->_intercept(__FUNCTION__, func_get_args());
    }

    /**
     * doc comment of getSub
     */
      public function getSub($a, $b)
    {
        return $this->_intercept(__FUNCTION__, func_get_args());
    }

    public function returnValue(null|\Ray\Aop\FakeNum $num = NULL)
    {
        return $this->_intercept(__FUNCTION__, func_get_args());
    }

    public function getPrivateVal()
    {
        return $this->_intercept(__FUNCTION__, func_get_args());
    }
}