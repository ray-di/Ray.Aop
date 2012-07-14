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
        $function = function($object, $method, $params){
            return array($params[0] * 2);
        };
        $weaver = $this->weaver;
        $actual = $weaver($function, 'getDouble', array(2));
        $this->assertSame(32, $actual);
    }

    /**
     * Use invoke for "named parameter" or "validation" or whatever you want modify/check parameters.
     */
    public function test_invoke2()
    {
        $function = function($object, $method, $params){
            return array($params[0] * 2);
        };
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
        $weaver->not_exists;
    }

    public function test_toString()
    {
        $string = (string) $this->weaver;
        $this->assertSame('toStringString', $string);
    }

    public function test_offsetExists()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $this->assertTrue(isset($weaver['key']));
    }

    public function test_offsetGet()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $this->assertSame(10, $weaver['key']);
    }

    public function test_offsetSet()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $weaver['key'] = 20;
        $this->assertSame(20, $weaver['key']);
    }

    public function test_offsetUnset()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        unset($weaver['key']);;
        $this->assertFalse(isset($weaver['key']));
    }

    /**
     * @expectedException RuntimeException
     */
    public function test_offsetExistsException()
    {
        $weaver = $this->weaver;
        isset($weaver['key']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function test_offsetGetException()
    {
        $weaver = $this->weaver;
        $weaver['key'];
    }

    /**
     * @expectedException RuntimeException
     */
    public function test_offsetSetException()
    {
        $weaver = $this->weaver;
        $weaver['key'] = 20;
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function test_offsetUnsetException()
    {
        unset($weaver['key']);
    }

    /**
     * @test
     */
    public function ___getObject()
    {
        $object = $this->weaver->___getObject();
        $this->assertInstanceOf('Ray\Aop\MockMethod', $object);
    }

    /**
     * @test
     */
    public function ___getBind()
    {
        $bind = $this->weaver->___getBind();
        $this->assertInstanceOf('Ray\Aop\Bind', $bind);
    }
    
    public function test_addPublicProperty()
    {
        $this->weaver->a = 1;
        $object = $this->weaver->___getObject();
        $this->assertSame(1, $object->a);
    }
}
