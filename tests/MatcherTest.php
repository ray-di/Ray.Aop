<?php
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

class MatcherTestSuperClass {}
class MatcherTestChildClass extends MatcherTestSuperClass{}
class MatcherTestIsolateClass {}

/**
* Test class for Ray.Aop
*/
class MatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $matcher;

    protected function setUp()
    {
        parent::setUp();
        $reader = new Reader;
        $this->matcher = new Matcher($reader);
    }

    public function test_New()
    {
        $this->assertInstanceOf('Ray\Aop\Matcher', $this->matcher);
    }

    public function test_Any()
    {
        $any = $this->matcher->any();
        $result = $any('anyClassEvenDoesNotExists', 'Ray\Aop\Tests\Mock\AnnotateClass');
        $this->assertTrue($result);
    }

    public function test_annotatedWithClass()
    {
        $annotation = 'Ray\Aop\Tests\Annotation\Resource';
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $match = $this->matcher->annotatedWith($annotation);
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertTrue($result);
    }

    public function test_annotatedWithClassReturnMatcherClass()
    {
        $annotation = 'Ray\Aop\Tests\Annotation\Resource';
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $match = $this->matcher->annotatedWith($annotation);
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertSame(true, $result);
    }

    public function test_annotatedWithMethod()
    {
        $annotation = 'Ray\Aop\Tests\Annotation\Marker';
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $matcher = $this->matcher->annotatedWith($annotation);
        $this->assertInstanceOf('Ray\Aop\Matcher', $matcher);
        $matchedArray = $matcher($class, Matcher::TARGET_METHOD);
        $matchedFirst = $matchedArray[0];
        $this->assertInstanceOf('Ray\Aop\Matched', $matchedFirst);
        $this->assertSame('getDouble', $matchedFirst->methodName);
        $this->assertInstanceOf('Ray\Aop\Tests\Annotation\Marker', $matchedFirst->annotation);
    }

    public function test_SubclassesOf()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildClass';
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertTrue($result);
    }

    public function test_SubclassesOf_withSameClass()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestSuperClass';
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertTrue($result);
    }

    public function test_SubclassesOfFalse()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildXXXX';
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertFalse($result);
    }

    /**
* @expectedException \Ray\Aop\Exception\InvalidArgument
*/
    public function test_SubclassesOfThrowExceptionIfTargetIsMethod()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $result = $match($class, Matcher::TARGET_METHOD);
        $this->assertFalse($result);
    }

    public function test_toString()
    {
        $matcher = clone $this->matcher;
        $this->assertSame(':null', (string) $matcher);
    }

    /**
* start '__' prefix method does not match
*/
    public function test_AnyButNotStartWithDoubleUnderscore()
    {
        $any = $this->matcher->any();
        $result = $any('__construct', Matcher::TARGET_METHOD);
        $this->assertFalse($result);
    }

    /**
* ArrayObject interface method does not match
*/
    public function test_AnyButNotArrayAccessMethod()
    {
        $any = $this->matcher->any();
        $methods = (new \ReflectionClass('ArrayObject'))->getMethods();
        foreach ($methods as $method) {
            $result = $any($method->name, Matcher::TARGET_METHOD);
            $this->assertFalse($result);
        }
    }

    public function test_isStartWithMethodTrue()
    {
        $startWith = $this->matcher->startWith('get');
        $result = $startWith('getSub', Matcher::TARGET_METHOD);
        $this->assertTrue($result);
    }

    public function test_isStartWithMethodFalse()
    {
        $startWith = $this->matcher->startWith('on');
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $result = $startWith($class, Matcher::TARGET_METHOD, '__construct');
        $this->assertFalse($result);
    }
}
