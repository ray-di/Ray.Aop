<?php

namespace Ray\Aop;

class NamedArgsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NamedArgs
     */
    protected $args;

    protected function setUp()
    {
        $this->args = new NamedArgs;
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Aop\NamedArgs', $this->args);
    }

    public function testGet()
    {
        $invocation = new ReflectiveMethodInvocation([new FakeClass, 'getSub'], [1, 2]);
        $namedArgs = $this->args->get($invocation);
        $this->assertSame(1, $namedArgs['a']);
    }

    public function testDuplicatedParamName()
    {
        if (! defined('HHVM_VERSION')) {
            $this->setExpectedException('Ray\Aop\Exception\DuplicatedNamedParam');
        }
        $invocation = new ReflectiveMethodInvocation([new FakeClass, 'duplicatedParamName'], [1, 2]);
        $this->args->get($invocation);
    }
}
