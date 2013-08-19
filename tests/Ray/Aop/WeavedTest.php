<?php

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Mock\MockMethod___weaved;
use Ray\Aop\Mock\Weaved;

class WeavedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Weaved
     */
    protected $weaved;

    protected function setUp()
    {
        parent::setUp();
        $this->weaved = new Weaved;
    }

    public function testImplicitBiding()
    {
        $bind = (new Bind)->bindInterceptors('returnSame', [new DoubleInterceptor]);
        $this->weaved->___postConstruct($bind);
        $result = $this->weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testImplicitBidingDoubleInterceptor()
    {
        $bind = (new Bind)->bindInterceptors('returnSame', [new DoubleInterceptor, new DoubleInterceptor]);
        $this->weaved->___postConstruct($bind);
        $result = $this->weaved->returnSame(1);
        $this->assertSame(4, $result);
    }

    public function testNoInterceptor()
    {
        $actual = $this->weaved->getSub(3, 2);
        $this->assertSame(1, $actual);
    }

    public function testMatcherWeave()
    {
        $matcher = new Matcher(new AnnotationReader);
        $pointcut = new Pointcut(
            $matcher->any(),
            $matcher->any(),
            [new DoubleInterceptor]
        );
        $bind = new Bind;
        $bind->bind('Ray\Aop\Mock\Weaved', [$pointcut]);
        $this->weaved->___postConstruct($bind);
        $actual = $this->weaved->returnSame(1);
        $this->assertSame(2, $actual);
    }


}

