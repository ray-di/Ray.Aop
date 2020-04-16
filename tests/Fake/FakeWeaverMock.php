<?php
namespace Ray\Aop;

/** doc comment of FakeMock */
class FakeWeaverMock
{
    private $a = 1;

    protected $b = 2;

    public $c = 3;

    /**
     * doc comment of returnSame
     */
    public function returnSame($a)
    {
        return $a;
    }

    /**
     * doc comment of getSub
     */
    public function getSub($a, $b)
    {
        return $a - $b;
    }

    public function returnValue(FakeNum $num = null)
    {
        return ($num instanceof FakeNum) ? $num->value : null;
    }

    public function getPrivateVal()
    {
        return $this->a;
    }
}
