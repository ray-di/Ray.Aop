<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as StandardPrinter;
use Ray\Aop\Interceptor\AbortProceedInterceptor;
use Ray\Aop\Interceptor\DoubleInterceptor;
use TokenReflection\ReflectionClass;

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
            __DIR__ . '/tmp',
            new StandardPrinter
        );
        $this->bind = new Bind;
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor]);
        $this->bind->bind(FakeWeaved::class, [$pointcut]);
    }


    public function testBuildClass()
    {
        $class = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));

        return $class;
    }

    public function testBuildClassTwice()
    {
        $class1 = $this->compiler->compile(FakeMock::class, $this->bind);
        $class2 = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class1));
        $this->assertSame($class1, $class2);
        $class1File = (new \ReflectionClass($class1))->getFileName();
        $class2File = (new \ReflectionClass($class1))->getFileName();
        $this->assertSame($class1File, $class2File);
    }


    /**
     * @depends testBuildClass
     */
    public function testBuild($class)
    {
        $parentClass = (new \ReflectionClass($class))->getParentClass()->name;
        $this->assertSame($parentClass, FakeMock::class);
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
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $parent = (new \ReflectionClass($weaved))->getParentClass()->name;
        $this->assertSame($parent, FakeMock::class);

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
        $num = new FakeNum;
        $num->value = 1;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testGetPrivateVal()
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /* @var $weaved \Ray\Aop\FakeMock */
        $val = $weaved->getPrivateVal();
        $this->assertSame($val, 1);
    }

    public function testCallAbortProceedInterceptorTwice()
    {
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeAbortProceedInterceptor]);
        $this->bind->bind(FakeWeaved::class, [$pointcut]);
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /* @var $weaved \Ray\Aop\FakeMock */
        $this->assertSame(40, $weaved->returnSame(1));
        $this->assertSame(40, $weaved->returnSame(1));
    }

    public function testClassDocComment()
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /* @var $weaved \Ray\Aop\FakeMock */
        $docComment = (new \ReflectionClass($weaved))->getDocComment();
        $expected = (new \ReflectionClass(FakeMock::class))->getDocComment();
        $this->assertContains('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testMethodDocComment()
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        /* @var $weaved \Ray\Aop\FakeMock */
        $docComment = (new \ReflectionClass($weaved))->getMethods()[0]->getDocComment();
        $expected = (new \ReflectionClass(FakeMock::class))->getMethods()[0]->getDocComment();

        $this->assertContains('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testNoDocComment()
    {
        $weaved = $this->compiler->newInstance(FakeMockNoDoc::class, [], $this->bind);
        /* @var $weaved \Ray\Aop\FakeMock */
        $classDocComment = (new \ReflectionClass($weaved))->getDocComment();
        $methodDocComment = (new \ReflectionClass($weaved))->getMethods()[0]->getDocComment();

        $this->assertFalse($classDocComment);
        $this->assertFalse($methodDocComment);
    }

    public function testSerialize()
    {
        $compiler = unserialize(serialize($this->compiler));
        $class = $compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));
    }

    public function testIncludeCompilerFile()
    {
        // new aop class file saved.
        passthru('php ' . __DIR__ . '/script/compile.php');
        // include class file.
        $class = require __DIR__ . '/script/compile.php';
        $this->assertStringEndsWith('RayAop', $class);
    }
}
