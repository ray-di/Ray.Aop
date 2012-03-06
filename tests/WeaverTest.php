<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;
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
        $bind->bindInterceptors('getDouble', array(new DoubleInterceptor));
        $this->weaver = new Weaver(new MockMethod, $bind);
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(8, $actual);
            }

    public function test_MatcherWeaveWithMultipleInterceptor()
    {
        $bind = new Bind;
        $bind->bindInterceptors('getDouble', array(new DoubleInterceptor, new DoubleInterceptor, new DoubleInterceptor, new DoubleInterceptor));
        $this->weaver = new Weaver(new MockMethod, $bind);
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(64, $actual);
    }

    /**
     * Use invoke for "named parameter" or "validation" or whatever you want modify/check parameters.
     */
    public function test_invoke()
    {
        $function = function($object, $method, $params){ return array($params[0] * 2);};
        $weaver = $this->weaver;
        $actual = $weaver($function, 'getDouble', array(2));
        $this->assertSame(32, $actual);
    }

    /**
     * Use invoke for "named parameter" or "validation" or whatever you want modify/check parameters.
     */
    public function test_invoke2()
    {
        $function = function($object, $method, $params){ return array($params[0] * 2);};
        $actual = $this->weaver->__invoke($function, 'getDouble', array(2));
        $this->assertSame(32, $actual);
    }

    public function test__get()
    {
        $weaver = $this->weaver;
        $this->assertSame('hello', $weaver->msg);
    }

    /**
     * @expectedException Ray\Aop\Exception\UndefinedProperty
     *
     */
    public function test__getNotExist()
    {
        $weaver = $this->weaver;
        v($weaver->not_exit_property);
    }
}