<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Aop\Interceptor\DoubleInterceptor;
use Ray\Aop\Interceptor\voidInterceptor;

class parentClass
{
}

class childClass extends parentClass
{
    public function method()
    {
    }
}

/**
 * Test class for Ray.Aop
 * @property mixed interceptors
 */

class BindTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bind
     */
    protected $bind;

    /**
     * target method is:
     *
     * $mock = new Mock;
     * $mock->add(2);
     */
    protected function setUp()
    {
        parent::setUp();
        $this->bind = new Bind;
        $this->interceptors = [new DoubleInterceptor, new DoubleInterceptor];
    }

    public function testBindInterceptors()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $this->assertSame($this->bind['getDouble'], $this->interceptors);
    }

    public function testBindAnyAny()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $result = $this->bind->bind($class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnySubClassOf()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->subclassesOf('Ray\Aop\parentClass'), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\childClass';
        $result = $this->bind->bind($class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('method', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnyAnnotatedWith()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\Marker';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $result = $this->bind->bind($class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnyAnnotatedWithAnnotation()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\Marker';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $bind = $this->bind->bind($class, [$pointcut]);
        /* @var $bind Bind */
        /** @noinspection PhpParamsInspection */
        list($method,) = each($bind);
        $annotation = $bind->annotation[$method];
        $this->assertInstanceOf('Ray\Aop\Annotation\Marker', $annotation);
    }

    public function testBindAnyAnnotatedWithDoubleBind()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\Marker';
        $interceptors1 = [new voidInterceptor, new DoubleInterceptor];
        $interceptors2 = [new DoubleInterceptor, new voidInterceptor];

        $pointcut1 = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $interceptors1);
        $pointcut2 = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $interceptors2);
        $this->bind->bind($class, [$pointcut1]);
        $result = $this->bind->bind($class, [$pointcut2]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertInstanceOf('Ray\Aop\Interceptor\voidInterceptor', $interceptors[0]);
        $this->assertInstanceOf('Ray\Aop\Interceptor\DoubleInterceptor', $interceptors[1]);
        $this->assertInstanceOf('Ray\Aop\Interceptor\DoubleInterceptor', $interceptors[2]);
        $this->assertInstanceOf('Ray\Aop\Interceptor\voidInterceptor', $interceptors[3]);
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidAnnotation
     */
    public function testBindAnyAnnotatedWithInvalidAnnotationName()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\AnnotationNotExistXXX';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $result = $this->bind->bind($class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testToString()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $this->assertInternalType('string', (string)$this->bind);
    }

    public function testHasBindingReturnTrue()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->subclassesOf('Ray\Aop\parentClass'), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\childClass';
        $this->bind->bind($class, [$pointcut]);
        $this->assertTrue($this->bind->hasBinding());
    }

    public function testHasBindingReturnFalse()
    {
        $this->assertFalse($this->bind->hasBinding());
    }

    public function testInvoke()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $bind = $this->bind;
        $interceptors = $bind('getDouble');
        $this->assertSame(2, count($interceptors));
        $this->assertInstanceOf('Ray\Aop\Interceptor\DoubleInterceptor', $interceptors[0]);
    }

    /**
     * Annotation does not match, no binding.
     */
    public function testBindByAnnotateBinding()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\Resource';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $this->bind->bind($class, [$pointcut]);
        $this->assertSame(0, (count($this->bind)));
    }

    public function testSerializable()
    {
        $serialized = serialize($this->bind);
        $this->assertInternalType('string', $serialized);

        return $serialized;
    }

    /**
     * @depends testSerializable
     */
    public function testUnserialize($data)
    {
        $data = unserialize($data);
        $this->assertTrue($data instanceof Bind);
    }

    public function testNotClassMatch()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Annotation\Resource';
        $pointcut = new Pointcut($matcher->startWith('XXX'), $matcher->startWith('XXX'), $this->interceptors);
        $this->bind->bind($class, [$pointcut]);
        $this->assertSame(0, (count($this->bind)));
    }
}
