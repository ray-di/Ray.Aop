<?php
namespace Ray\Aop;

class PointcutTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $pointCunt = new Pointcut(
            new BuiltinMatcher('startsWith', ['Ray']),
            new BuiltinMatcher('startsWith', ['get']),
            [new FakeInterceptor()]
        );
        $this->assertInstanceOf(Pointcut::class, $pointCunt);
    }
}
