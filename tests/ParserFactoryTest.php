<?php

namespace Ray\Aop;

class ParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $this->assertInstanceOf('PhpParser\Parser', ParserFactory::create());
    }
}
