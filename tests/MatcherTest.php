<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class MatcherTestSuperClass {}
class MatcherTestChildeClass extends MatcherTestSuperClass{}
class MatcherTestIsoleteClass {}


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
        $result = $any('anyClassEvenDoesntExists', 'Ray\Aop\Tests\Mock\AnnotateClass');
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

    public function test_annotatedWithMethod()
    {
        $annotation = 'Ray\Aop\Tests\Annotation\Marker';
        $class = 'Ray\Aop\Tests\Mock\AnnotateClass';
        $match = $this->matcher->annotatedWith($annotation);
        $result = $match($class, Matcher::TARGET_METHOD);
        $this->assertSame(1, count(1));
        $matched = $result[0];
        $this->assertInstanceOf('Ray\Aop\Matched', $matched);
        $this->assertSame('getDobule', $matched->methodName);
        $this->assertInstanceOf('Ray\Aop\Tests\Annotation\Marker', $matched->annotation);
    }

    public function test_SubclassesOf()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildeClass';
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertTrue($result);
    }

    public function test_SubclassesOfFalse()
    {
        $match = $this->matcher->subclassesOf('Ray\Aop\MatcherTestSuperClass');
        $class = 'Ray\Aop\MatcherTestChildeXXXX';
        $result = $match($class, Matcher::TARGET_CLASS);
        $this->assertFalse($result);
    }

}