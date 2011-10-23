<?php

namespace Ray\Aop;

/**
 * Test class for Ray.Aop
 */
class WeaverTest extends \PHPUnit_Framework_TestCase
{
    protected $invocation;

    /**
     * taget method is:
     *
     * $mock = new MockMethod;
     * $mock->add(2);
     */
    protected function setUp()
    {
        parent::setUp();
        $bind = new Bind;
        $bind->bindInterceptors('getDouble', array(new DoubleInterceptor, new DoubleInterceptor));
        $this->weaver = new Weaver(new MockMethod, $bind);
    }

    public function test_WithInterceptors()
    {
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(16, $actual);
    }

    public function test_WithoutInterceptor()
    {
        $actual = $this->weaver->getSub(3, 2);
        $this->assertSame(1, $actual);
    }

    /**
     * @expectedException BadFunctionCallException
     */
    public function test_NonExistMethodNameException()
    {
        $actual = $this->weaver->xxxxx(2);
    }

    public function test_MatcherWeave()
    {
        $bind = new Bind;
        $matcher = function($method) {
            return true;
        };
        $bind->bindMatcher($matcher, array(new DoubleInterceptor, new DoubleInterceptor, new DoubleInterceptor));
        $this->weaver = new Weaver(new MockMethod, $bind);
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(32, $actual);

    }

}