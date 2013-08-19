<?php

namespace Ray\Aop;

use Ray\Aop\Mock\Weaved;
use Doctrine\Common\Annotations\AnnotationReader;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Compiler
     */
    protected $compiler;

    protected function setUp()
    {
        parent::setUp();
        $this->compiler = new Compiler;
        $this->bind = new Bind;
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut(
            $matcher->any(),
            $matcher->startWith('return'),
            [new DoubleInterceptor]
        );
        $this->bind->bind('Ray\Aop\Mock\Weaved', [$pointcut]);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\Ray\Aop\Compiler', $this->compiler);
    }

    public function testBuildClass()
    {
        $class = $this->compiler->compile('\Ray\Aop\Mock\MockMethod', $this->bind);
        $this->assertTrue(class_exists($class));

        return $class;
    }

    /**
     * @depends testBuildClass
     */
    public function testBuild($class)
    {
        $parentClass = (new \ReflectionClass($class))->getParentClass()->name;
        $this->assertSame($parentClass, 'Ray\Aop\Mock\MockMethod');
    }

    /**
     * @depends testBuildClass
     */
    public function testBuildClassWeaved($class)
    {
        $weaved = new $class;
        $weaved->bind = $this->bind;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);

    }

    public function testNewInstance()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\MockMethod', [], $this->bind);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testWeaved()
    {
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut(
            $matcher->any(),
            $matcher->startWith('return'),
            [new DoubleInterceptor]
        );
        $weaved = new Weaved();
        $bind = new Bind;
        $bind->bind('Ray\Aop\Mock\Weaved', [$pointcut]);
        $weaved->___postConstruct($bind);
        $actual = $weaved->returnSame(1);
        $this->assertSame(2, $actual);
        $this->compiler->compile('\Ray\Aop\Mock\MockMethod', $bind);
    }
}

