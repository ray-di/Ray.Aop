<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Aop\Interceptor\DoubleInterceptor;
use Ray\Aop\Interceptor\VoidInterceptor;

class ParentClass
{
}

class ChildClass extends ParentClass
{
    public function method()
    {
    }
}

class BindTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bind
     */
    protected $bind;

    /**
     * @var array
     */
    protected $interceptors;

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
        $interceptors1 = [new VoidInterceptor, new DoubleInterceptor];
        $interceptors2 = [new DoubleInterceptor, new VoidInterceptor];

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
        $pointcut = new Pointcut($matcher->startsWith('XXX'), $matcher->startsWith('XXX'), $this->interceptors);
        $this->bind->bind($class, [$pointcut]);
        $this->assertSame(0, (count($this->bind)));
    }

    /**
     * @return array
     */
    public function logicalMethodMatchers()
    {
        $matcher = new Matcher(new Reader);

        return [
            [$matcher->logicalOr($matcher->annotatedWith('Ray\Aop\Annotation\Resource'), $matcher->annotatedWith('Ray\Aop\Annotation\Marker'))],
            [$matcher->logicalAnd($matcher->annotatedWith('Ray\Aop\Annotation\Marker'), $matcher->startsWith('getDouble'))],
            [$matcher->logicalXor($matcher->annotatedWith('Ray\Aop\Annotation\Marker'), $matcher->annotatedWith('Ray\Aop\Annotation\Resource'))],
            [$matcher->logicalNot($matcher->annotatedWith('Ray\Aop\Annotation\Resource'))],
        ];
    }

    /**
     * @param Matchable $logicalMethodMatcher
     * @dataProvider logicalMethodMatchers
     */
    public function testBindByLogicalBindingAsMethodMatcher(Matchable $logicalMethodMatcher)
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $pointcut = new Pointcut($matcher->any(), $logicalMethodMatcher, $this->interceptors);

        $result = $this->bind->bind($class, [$pointcut]);

        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindByMethodLogicalBinding()
    {
        $matcher = new Matcher(new Reader);
        $class = 'Ray\Aop\Mock\AnnotateClass';
        $pointcut = new Pointcut(
            $matcher->any(),
            $matcher->logicalOr(
                $matcher->annotatedWith('Ray\Aop\Annotation\Resource'),
                $matcher->any()
            ),
            $this->interceptors
        );

        $result = $this->bind->bind($class, [$pointcut]);

        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }
}
