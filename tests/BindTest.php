<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

class parentClass{}

class childClass extends parentClass
{
    public function method()
    {
    }
}

/**
 * Test class for Ray.Aop
 */

class BindTest extends \PHPUnit_Framework_TestCase
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
        $this->bind = new Bind;
        $this->interceptors = [new DoubleInterceptor, new DoubleInterceptor];
    }

    public function test_bindInterceptors()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $this->assertSame($this->bind['getDouble'], $this->interceptors);
    }

    public function test_bindAnyAny()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $result = $this->bind->bind($class, [$pointcut]);
        list($method, $interceptors) = each($result);
        $this->assertSame('getDobule', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function test_bindAnySubClassOf()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->subclassesOf('Ray\Aop\parentClass'), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\childClass';
        $result = $this->bind->bind($class, [$pointcut]);
        list($method, $interceptors) = each($result);
        $this->assertSame('method', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function test_bindAnyAnnotatedWith()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Tests\Annotation\Marker';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $result = $this->bind->bind($class, [$pointcut]);
        list($method, $interceptors) = each($result);
        $this->assertSame('getDobule', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function test_bindAnyAnnotatedWithAnnotation()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Tests\Annotation\Marker';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $bind = $this->bind->bind($class, [$pointcut]);
        /* @var $bind Bind */
        list($method, $interceptors) = each($bind);
        $annotation = $bind->annotation[$method];
        $this->assertInstanceOf('Ray\Aop\Tests\Annotation\Marker', $annotation);
    }

    public function test_bindAnyAnnotatedWithDoubleBind()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Tests\Annotation\Marker';
        $interceptors1 = [new voidInterceptor, new DoubleInterceptor];
        $interceptors2 = [new DoubleInterceptor, new voidInterceptor];

        $pointcut1 = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $interceptors1);
        $pointcut2 = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $interceptors2);
        $this->bind->bind($class, [$pointcut1]);
        $result = $this->bind->bind($class, [$pointcut2]);
        list($method, $interceptors) = each($result);
        $this->assertSame('getDobule', $method);
        $this->assertInstanceOf('Ray\Aop\voidInterceptor', $interceptors[0]);
        $this->assertInstanceOf('Ray\Aop\DoubleInterceptor', $interceptors[1]);
        $this->assertInstanceOf('Ray\Aop\DoubleInterceptor', $interceptors[2]);
        $this->assertInstanceOf('Ray\Aop\voidInterceptor', $interceptors[3]);
    }

    /**
     * @expectedException Ray\Aop\Exception\InvalidAnnotation
     */
    public function test_bindAnyAnnotatedWithInvalidAnnotationName()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Tests\Annotation\XXXXXXX';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $result = $this->bind->bind($class, [$pointcut]);
        list($method, $interceptors) = each($result);
        $this->assertSame('getDobule', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function test_toString()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $this->assertInternalType('string', (string) $this->bind);
    }

    public function test_hasBindingReturnTrue()
    {
        $matcher = new Matcher(new Reader);
        $pointcut = new Pointcut($matcher->subclassesOf('Ray\Aop\parentClass'), $matcher->any(), $this->interceptors);
        $class = 'Ray\Aop\childClass';
        $this->bind->bind($class, [$pointcut]);
        $this->assertTrue($this->bind->hasBinding());
    }

    public function test_hasBindingReturnFalse()
    {
        $this->assertFalse($this->bind->hasBinding());
    }

    public function test_invoke()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $bind = $this->bind;
        $interceptors = $bind('getDouble');
        $this->assertSame(2, count($interceptors));
        $this->assertInstanceOf('Ray\Aop\DoubleInterceptor', $interceptors[0]);
    }

    /**
     * Annoattion doesn't match, no bindig.
     */
    public function test_bindByAnnoateBindig()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $annotationName = 'Ray\Aop\Tests\Annotation\Resource';
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith($annotationName), $this->interceptors);
        $this->bind->bind($class, [$pointcut]);
        $this->assertSame(0, (count($this->bind)));
    }

    public function test_serializable()
    {
        $serialized = serialize($this->bind);
        $this->assertInternalType('string', $serialized);
        return $serialized;
    }

    /**
     * @depends test_serializable
     */
    public function test_unserialize($data)
    {
        $data = unserialize($data);
        $this->assertTrue($data instanceof \Ray\Aop\Bind);
    }
}
