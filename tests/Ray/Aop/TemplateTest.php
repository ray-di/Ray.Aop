<?php

namespace Ray\Aop\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Bind;
use Ray\Aop\Interceptor\DoubleInterceptor;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Weaved;

class TemplateTest extends \PHPUnit_Framework_TestCase
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
        $this->weaved->___bind = $bind;
        $result = $this->weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testImplicitBidingDoubleInterceptor()
    {
        $bind = (new Bind)->bindInterceptors('returnSame', [new DoubleInterceptor, new DoubleInterceptor]);
        $this->weaved->___bind = $bind;
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
        $pointcut = new Pointcut($matcher->any(), $matcher->any(), [new DoubleInterceptor]);
        $bind = new Bind;
        $bind->bind('Weaved', [$pointcut]);
        $this->weaved->___bind = $bind;
        $actual = $this->weaved->returnSame(1);
        $this->assertSame(2, $actual);
    }
}
