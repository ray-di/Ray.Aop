<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidSourceClassException;

class CodeGenTest extends TestCase
{
    /**
     * @var CodeGen
     */
    private $codeGen;

    protected function setUp() : void
    {
        $this->codeGen = new CodeGen((new ParserFactory)->newInstance(), new BuilderFactory, new Standard);
    }

    public function testTypeDeclarations()
    {
        $bind = new Bind;
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate('a', new \ReflectionClass(FakePhp7Class::class), $bind);
        $expected = 'function run(string $a, int $b, float $c, bool $d) : array';
        $this->assertContains($expected, $code);
    }

    public function testReturnType()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnTypeArray', []);
        $code = $this->codeGen->generate('a', new \ReflectionClass(FakePhp7ReturnTypeClass::class), $bind);
        $expected = 'function returnTypeArray() : array';
        $this->assertContains($expected, $code);
    }

    public function testInvalidSourceClass()
    {
        $this->expectException(InvalidSourceClassException::class);
        $this->codeGen->generate('a', new \ReflectionClass(\stdClass::class), new Bind);
    }
}
