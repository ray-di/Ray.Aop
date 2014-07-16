<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Interceptor\DoubleInterceptor;
use Ray\Aop\Interceptor\AbortProceedInterceptor;
use Ray\Aop\Mock\Num;
use PHPParser_Lexer;
use PHPParser_Parser;
use PHPParser_PrettyPrinter_Default;
use PHPParser_BuilderFactory;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bind
     */
    private $bind;

    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp()
    {
        parent::setUp();
        $this->compiler = new Compiler(
            __DIR__ . '/Weaved',
            new PHPParser_PrettyPrinter_Default
        );
        $this->bind = new Bind;
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new DoubleInterceptor]);
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

    public function testBuildClassTwice()
    {
        $class1 = $this->compiler->compile('\Ray\Aop\Mock\Mock', $this->bind);
        $class2 = $this->compiler->compile('\Ray\Aop\Mock\Mock', $this->bind);
        $this->assertTrue(class_exists($class1));
        $this->assertSame($class1, $class2);
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
        $weaved->rayAopBind = $this->bind;
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
    public function testMethodReturnValue($weaved)
    {
        $num = new Num;
        $num->value = 1;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testGetPrivateVal()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\Mock', [], $this->bind);
        /* @var $weaved \Ray\Aop\Mock\Mock */
        $val = $weaved->getPrivateVal();
        $this->assertSame($val, 1);
    }

    public function testCallAbortProceedInterceptorTwice()
    {
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new AbortProceedInterceptor]);
        $this->bind->bind('Ray\Aop\Mock\Weaved', [$pointcut]);
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\Mock', [], $this->bind);
        /* @var $weaved \Ray\Aop\Mock\Mock */
        $this->assertSame(40, $weaved->returnSame(1));
        $this->assertSame(40, $weaved->returnSame(1));
    }

    public function testClassDocComment()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\Mock', [], $this->bind);
        /* @var $weaved \Ray\Aop\Mock\Mock */
        $docComment = (new \ReflectionClass($weaved))->getDocComment();
        $expected = (new \ReflectionClass('Ray\Aop\Mock\Mock'))->getDocComment();
        $this->assertContains('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testMethodDocComment()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\Mock', [], $this->bind);
        /* @var $weaved \Ray\Aop\Mock\Mock */
        $docComment = (new \ReflectionClass($weaved))->getMethods()[0]->getDocComment();
        $expected = (new \ReflectionClass('Ray\Aop\Mock\Mock'))->getMethods()[0]->getDocComment();

        $this->assertContains('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testNoDocComment()
    {
        $weaved = $this->compiler->newInstance('Ray\Aop\Mock\MockNoDoc', [], $this->bind);
        /* @var $weaved \Ray\Aop\Mock\Mock */
        $classDocComment = (new \ReflectionClass($weaved))->getDocComment();
        $methodDocComment = (new \ReflectionClass($weaved))->getMethods()[0]->getDocComment();

        $this->assertFalse($classDocComment);
        $this->assertFalse($methodDocComment);
    }

    public function testSerialize()
    {
        $compiler = unserialize(serialize($this->compiler));
        $class = $compiler->compile('\Ray\Aop\Mock\Mock', $this->bind);
        $this->assertTrue(class_exists($class));
    }

    public function testGetAopClassDir()
    {
        $dir = $this->compiler->getAopClassDir();
        $this->assertSame(__DIR__ . '/Weaved', $dir);
    }

}
