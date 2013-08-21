<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Interceptor\DoubleInterceptor;
use Ray\Aop\Mock\Num;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Compiler
     */
    protected $compiler;

    protected function setUp()
    {
        parent::setUp();
        $this->compiler = new Compiler(__DIR__ . '/Weaved');
        $this->bind = new Bind;
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut($matcher->any(), $matcher->startWith('return'), [new DoubleInterceptor]);
        $this->bind->bind('Ray\Aop\Mock\Weaved', [$pointcut]);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\Ray\Aop\Compiler', $this->compiler);
    }

    public function testBuildClass()
    {
        $class = $this->compiler->compile('\Ray\Aop\Mock\Mock', $this->bind);
        $this->assertTrue(class_exists($class));

        return $class;
    }

    /**
     * @depends testBuildClass
     */
    public function testBuild($class)
    {
        $parentClass = (new \ReflectionClass($class))->getParentClass()->name;
        $this->assertSame($parentClass, 'Ray\Aop\Mock\Mock');
    }

    /**
     * @depends testBuildClass
     */
    public function testBuildClassWeaved($class)
    {
        $weaved = new $class;
        $weaved->___bind = $this->bind;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testNewInstance()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\Mock', [], $this->bind);
        $parent = (new \ReflectionClass($weaved))->getParentClass()->name;
        $this->assertSame($parent, 'Ray\Aop\Mock\Mock');

        return $weaved;
    }

    /**
     * @depends testNewInstance
     */
    public function testWeavedInterceptorWorks($weaved)
    {
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * @depends testNewInstance
     */
    public function estMethodReturnValue($weaved)
    {
        $num = new Num;
        $num->value = 1;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }
}
