<?php

namespace Ray\Aop;

use Ray\Aop\Interceptor\DoubleInterceptor;

class WeaverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Weaver
     */
    protected $weaver;

    /**
     * target method is:
     *
     * $mock = new Mock;
     * $mock->add(2);
     */
    protected function setUp()
    {
        parent::setUp();
        $bind = new Bind;
        $bind->bindInterceptors('getDouble', [new DoubleInterceptor, new DoubleInterceptor]);
        $this->weaver = new Weaver(new MockMethod, $bind);
    }

    public function testWithInterceptors()
    {
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(16, $actual);
    }

    public function testWithoutInterceptor()
    {
        $actual = $this->weaver->getSub(3, 2);
        $this->assertSame(1, $actual);
    }

    /**
     * @expectedException \BadFunctionCallException
     */
    public function test_NonExistMethodNameException()
    {
        $this->weaver->xxxxx(2);
    }

    public function testMatcherWeave()
    {
        $bind = new Bind;
        $bind->bindInterceptors('getDouble', [new DoubleInterceptor]);
        $this->weaver = new Weaver(new MockMethod, $bind);
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(8, $actual);
    }

    public function testMatcherWeaveWithMultipleInterceptor()
    {
        $bind = new Bind;
        $bind->bindInterceptors(
            'getDouble',
            [new DoubleInterceptor, new DoubleInterceptor, new DoubleInterceptor, new DoubleInterceptor]
        );
        $this->weaver = new Weaver(new MockMethod, $bind);
        $actual = $this->weaver->getDouble(2);
        $this->assertSame(64, $actual);
    }

    /**
     * Use invoke for "named parameter" or "validation" or whatever you want modify/check parameters.
     */
    public function testInvoke()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $function = function ($object, $method, $params) {
            return [$params[0] * 2];
        };
        $weaver = $this->weaver;
        $actual = $weaver($function, 'getDouble', [2]);
        $this->assertSame(32, $actual);
    }

    /**
     * Use invoke for "named parameter" or "validation" or whatever you want modify/check parameters.
     */
    public function testInvoke2()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $function = function ($object, $method, $params) {
            return [$params[0] * 2];
        };
        $actual = $this->weaver->__invoke($function, 'getDouble', [2]);
        $this->assertSame(32, $actual);
    }

    public function testGet()
    {
        $weaver = $this->weaver;
        $this->assertSame('hello', $weaver->msg);
    }

    /**
     * @expectedException \Ray\Aop\Exception\UndefinedProperty
     *
     */
    public function testGetNotExist()
    {
        $weaver = $this->weaver;
        $weaver->not_exists;
    }

    public function testToString()
    {
        $string = (string)$this->weaver;
        $this->assertSame('toStringString', $string);
    }

    public function testOffsetExists()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $this->assertTrue(isset($weaver['key']));
    }

    public function testOffsetGet()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $this->assertSame(10, $weaver['key']);
    }

    public function testOffsetSet()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        $weaver['key'] = 20;
        $this->assertSame(20, $weaver['key']);
    }

    public function testOffsetUnset()
    {
        $weaver = new Weaver(new \ArrayObject(['key' => 10]), new Bind);
        unset($weaver['key']);
        $this->assertFalse(isset($weaver['key']));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOffsetExistsException()
    {
        $weaver = $this->weaver;
        /** @noinspection PhpExpressionResultUnusedInspection */
        isset($weaver['key']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOffsetGetException()
    {
        $weaver = $this->weaver;
        $weaver['key'];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOffsetSetException()
    {
        $weaver = $this->weaver;
        $weaver['key'] = 20;
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testOffsetUnsetException()
    {
        /** @noinspection PhpUndefinedVariableInspection */
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

    public function testAddPublicProperty()
    {
        $this->weaver->a = 1;
        $object = $this->weaver->___getObject();
        $this->assertSame(1, $object->a);
    }
}
