<?php

namespace Ray\Aop;

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;

class CodeGenTest extends \PHPUnit_Framework_TestCase
{
    public function testAddNullDefaultWithAssisted()
    {
        $codeGen = new CodeGen(ParserFactory::create(), new BuilderFactory, new Standard);
        $bind = new Bind;
        $bind->bindInterceptors('run', []);
        $code = $codeGen->generate('a', new \ReflectionClass(FakeAssistedConsumer::class), $bind);
        $expected = 'function run($a, $b = null, $c = null)';
        $this->assertContains($expected, $code);
    }
}
