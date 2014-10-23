<?php
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

class MatcherTestSuperClass
{
}

class MatcherTestChildClass extends MatcherTestSuperClass
{
    public function get(){}
}

class MatcherTestIsolateClass
{
}

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Matcher
     */
    protected $matcher;

    protected function setUp()
    {
        parent::setUp();
        $reader = new Reader;
        $this->matcher = new Matcher($reader);
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Aop\Matcher', $this->matcher);
    }

    public function testAny()
    {
        $any = $this->matcher->any();
        $result = $any('anyClassEvenDoesNotExists', 'Ray\Aop\FakeAnnotateClass');
        $this->assertTrue($result);
    }

    public function testAnnotatedWithClass()
    {
        $annotation = 'Ray\Aop\FakeResource';
        $class = 'Ray\Aop\FakeAnnotateClass';
        $match = $this->matcher->annotatedWith($annotation);
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testAnnotatedWithClassReturnMatcherClass()
    {
        $annotation = 'Ray\Aop\FakeResource';
        $class = 'Ray\Aop\FakeAnnotateClass';
        $match = $this->matcher->annotatedWith($annotation);
        $result = $match($class, Target::IS_CLASS);
        $this->assertSame(true, $result);
    }

    public function testAnnotatedWithMethod()
    {
        $annotation = 'Ray\Aop\FakeMarker';
        $class = 'Ray\Aop\FakeAnnotateClass';
        $matcher = $this->matcher->annotatedWith($annotation);
        $this->assertInstanceOf('Ray\Aop\Matcher', $matcher);
        $matchedArray = $matcher($class, Matcher::TARGET_METHOD);
        $matchedFirst = $matchedArray[0];
        $this->assertInstanceOf('Ray\Aop\Matched', $matchedFirst);
        $this->assertSame('getDouble', $matchedFirst->methodName);
        $this->assertInstanceOf('Ray\Aop\FakeMarker', $matchedFirst->annotation);
    }

    public function testSubclassesOf()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testSubclassesOf_withSameClass()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestSuperClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testSubclassesOfFalse()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildXXXX';
        $result = $match($class, Target::IS_CLASS);
        $this->assertFalse($result);
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidArgument
     */
    public function testSubclassesOfThrowExceptionIfTargetIsMethod()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\FakeAnnotateClass';
        $result = $match($class, Matcher::TARGET_METHOD);
        $this->assertFalse($result);
    }

    /**
     * start '__' prefix method does not match
     */
    public function testAnyButNotstartsWithDoubleUnderscore()
    {
        $any = $this->matcher->any();
        $result = $any('__construct', Matcher::TARGET_METHOD);
        $this->assertFalse($result);
    }

    /**
     * ArrayObject interface method does not match
     */
    public function testAnyButNotArrayAccessMethod()
    {
        $any = $this->matcher->any();
        $methods = (new \ReflectionClass('ArrayObject'))->getMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $result = $any($methodName, Matcher::TARGET_METHOD);
            $this->assertFalse($result);
        }
    }

    public function testIsstartsWithMethodTrue()
    {
        $startsWith = $this->matcher->startsWith('get');
        $result = $startsWith('getSub', Matcher::TARGET_METHOD);
        $this->assertTrue($result);
    }

    public function testIsstartsWithMethodFalse()
    {
        $startsWith = $this->matcher->startsWith('on');
        $class = 'Ray\Aop\FakeAnnotateClass';
        $result = $startsWith($class, Matcher::TARGET_METHOD, '__construct');
        $this->assertFalse($result);
    }

    public function testIsStartsWith()
    {
        $startsWith = $this->matcher->startsWith('on');
        $class = 'Ray\Aop\FakeAnnotateClass';
        $result = $startsWith($class, Matcher::TARGET_METHOD, '__construct');
        $this->assertFalse($result);
    }


    public function testIsLogicalOrAnyOrAny()
    {
        $match = $this->matcher->logicalOr($this->matcher->any(), $this->matcher->any());
        $class = 'Ray\Aop\XXX';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalOrTrueOrTrue()
    {
        $match = $this->matcher->logicalOr(
            $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass'),
            $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalOrFalseOrTrue()
    {
        $match = $this->matcher->logicalOr(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalOrFalseOrFalse()
    {
        $match = $this->matcher->logicalOr(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertFalse($result);
    }

    public function testIsLogicalAndFalseAndTrue()
    {
        $match = $this->matcher->logicalAnd(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertFalse($result);
    }

    public function testIsLogicalXorFalseXorTrue()
    {
        $match = $this->matcher->logicalXor(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalNot()
    {
        $match = $this->matcher->logicalNot(
            $this->matcher->subclassesOf('Ray\Aop\XXX')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalThreeArguments()
    {
        $match = $this->matcher->logicalOr(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->any()
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalOrFourArgs()
    {
        $match = $this->matcher->logicalOr(
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->any()
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    public function testIsLogicalAndThreeArgs()
    {
        $match = $this->matcher->logicalAnd(
            $this->matcher->any(),
            $this->matcher->any(),
            $this->matcher->subclassesOf('Ray\Aop\XXX')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertFalse($result);
    }

    public function testIsLogicalXorTreeArgs()
    {
        $match = $this->matcher->logicalXor(
            $this->matcher->any(),
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX'),
            $this->matcher->subclassesOf('Ray\Aop\XXX')
        );
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Ray\Aop\Exception\InvalidArgument
     */
    public function testIsSubclassesOfException()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $method = new \ReflectionMethod('Ray\Aop\MatcherTestChildClass', 'get');
        $match($method, Matcher::TARGET_METHOD);
    }

    public function testNoAnnotationReaderConstruct()
    {
        $matcher = new Matcher;
        $this->assertInstanceOf('Ray\Aop\Matcher', $matcher);
    }

    public function MyMatcherBind()
    {
        $match = (new FakeMyMatcher)->endWith('Resource');
        $class = 'Ray\Aop\MatcherResource';
        $result = $match($class, Target::IS_CLASS);
        $this->assertTrue($result);
    }

}
