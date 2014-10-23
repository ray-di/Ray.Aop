<?php

namespace Ray\Aop;

class FakeParentClass
{
}

class FakeChildClass extends FakeParentClass
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
        $this->interceptors = [new FakeDoubleInterceptor, new FakeDoubleInterceptor];
    }

    public function testBindInterceptors()
    {
        $this->bind->bindInterceptors('getDouble', $this->interceptors);
        $this->assertSame($this->bind['getDouble'], $this->interceptors);
    }

    public function testBindAnyAny()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), $this->interceptors);
        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnySubClassOf()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->subclassesOf(FakeParentClass::class), $matcher->any(), $this->interceptors);
        $result = $this->bind->bind(FakeChildClass::class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('method', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnyAnnotatedWith()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith(FakeMarker::class), $this->interceptors);
        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindAnyAnnotatedWithAnnotation()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith(FakeMarker::class), $this->interceptors);
        $bind = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        /* @var $bind Bind */
        /** @noinspection PhpParamsInspection */
        list($method,) = each($bind);
        $annotation = $this->bind->annotation[$method];
        $this->assertInstanceOf('Ray\Aop\FakeMarker', $annotation);
    }

    public function testBindAnyAnnotatedWithDoubleBind()
    {
        $matcher = new Matcher;
        $interceptors1 = [new FakeVoidInterceptor, new FakeDoubleInterceptor];
        $interceptors2 = [new FakeDoubleInterceptor, new FakeVoidInterceptor];

        $pointcut1 = new Pointcut($matcher->any(), $matcher->annotatedWith(FakeMarker::class), $interceptors1);
        $pointcut2 = new Pointcut($matcher->any(), $matcher->annotatedWith(FakeMarker::class), $interceptors2);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut1]);
        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut2]);
        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertInstanceOf(FakeVoidInterceptor::class, $interceptors[0]);
        $this->assertInstanceOf(FakeDoubleInterceptor::class, $interceptors[1]);
        $this->assertInstanceOf(FakeDoubleInterceptor::class, $interceptors[2]);
        $this->assertInstanceOf(FakeVoidInterceptor::class, $interceptors[3]);
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidAnnotation
     */
    public function testBindAnyAnnotatedWithInvalidAnnotationName()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith('Ray\Aop\FakeAnnotationNotExistXXX'), $this->interceptors);
        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
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
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->subclassesOf(FakeParentClass::class), $matcher->any(), $this->interceptors);
        $this->bind->bind(FakeChildClass::class, [$pointcut]);
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
        $this->assertInstanceOf(FakeDoubleInterceptor::class, $interceptors[0]);
    }

    /**
     * Annotation does not match, no binding.
     */
    public function testBindByAnnotateBinding()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith(FakeResource::class), $this->interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
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
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->startsWith('XXX'), $matcher->startsWith('XXX'), $this->interceptors);
        $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);
        $this->assertSame(0, (count($this->bind)));
    }

    /**
     * @return array
     */
    public function logicalMethodMatchers()
    {
        $matcher = new Matcher;

        return [
            [$matcher->logicalOr($matcher->annotatedWith(FakeResource::class), $matcher->annotatedWith(FakeMarker::class))],
            [$matcher->logicalAnd($matcher->annotatedWith(FakeMarker::class), $matcher->startsWith('getDouble'))],
            [$matcher->logicalXor($matcher->annotatedWith(FakeMarker::class), $matcher->annotatedWith(FakeResource::class))],
            [$matcher->logicalNot($matcher->annotatedWith(FakeResource::class))],
        ];
    }

    /**
     * @param Matchable $logicalMethodMatcher
     * @dataProvider logicalMethodMatchers
     */
    public function testBindByLogicalBindingAsMethodMatcher(Matchable $logicalMethodMatcher)
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut($matcher->any(), $logicalMethodMatcher, $this->interceptors);

        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);

        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }

    public function testBindByMethodLogicalBinding()
    {
        $matcher = new Matcher;
        $pointcut = new Pointcut(
            $matcher->any(),
            $matcher->logicalOr(
                $matcher->annotatedWith('Ray\Aop\FakeResource'),
                $matcher->any()
            ),
            $this->interceptors
        );

        $result = $this->bind->bind(FakeAnnotateClass::class, [$pointcut]);

        /** @noinspection PhpParamsInspection */
        list($method, $interceptors) = each($result);
        $this->assertSame('getDouble', $method);
        $this->assertSame($this->interceptors, $interceptors);
    }
}
