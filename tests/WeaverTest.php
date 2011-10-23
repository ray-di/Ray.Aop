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
        $interceptors = array(new DoubleInterceptor, new DoubleInterceptor);
        $this->Weaver = new Weaver(new MockMethod, $interceptors);
    }

    public function test_call()
    {
        $actual = $this->Weaver->getDouble(2);
        $this->assertSame(16, $actual);
    }
}