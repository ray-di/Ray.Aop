<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

class ParserFactoryTest extends TestCase
{
    public function testCreate()
    {
        $this->assertInstanceOf('PhpParser\Parser', (new ParserFactory)->newInstance());
    }
}
