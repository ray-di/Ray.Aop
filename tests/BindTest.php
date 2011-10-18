<?php

namespace Ray\Aop;

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
        $this->intercetpors = array(new DoubleInterceptor, new DoubleInterceptor);
    }

    public function test_bindInterceptors()
    {
        $this->bind->bindInterceptors('getDouble', $this->intercetpors);
        $this->assertSame($this->bind['getDouble'], $this->intercetpors);
    }

    public function test_bindMatcherMatched()
    {
        // getXXX method ?
        $matcher = function($name) {
            return (substr($name, 0, 3) === 'get') ? true : false;
        };
        $this->bind->bindMatcher($matcher, $this->intercetpors);
        $bind = $this->bind;
        $result = $bind('getDouble');
        $this->assertSame($this->intercetpors, $result);
    }

    public function test_bindMatcherUnMatched()
    {
        // getXXX method ?
        $matcher = function($name) {
            return (substr($name, 0, 3) === 'get') ? true : false;
        };
        $this->bind->bindMatcher($matcher, $this->intercetpors);
        $bind = $this->bind;
        $result = $bind('xxx');
        $this->assertSame(false, $result);
    }


    public function test_toString()
    {
        $this->bind->bindInterceptors('getDouble', $this->intercetpors);
        $expected = '[getDouble]=>[Ray\Aop\DoubleInterceptor,Ray\Aop\DoubleInterceptor]' . "\n";
        $this->assertSame($expected, (string)$this->bind);
    }
}