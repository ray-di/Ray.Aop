<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
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
        $this->codeGen = new CodeGen((new ParserFactory)->newInstance(), new BuilderFactory, new AopClassName(''));
    }

    public function testTypeDeclarations() : void
    {
        $bind = new Bind;
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp7Class::class), $bind);
        $expected = 'function run(string $a, int $b, float $c, bool $d) : array';
        $this->assertStringContainsString($expected, $code->code);
    }

    public function testReturnType() : void
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnTypeArray', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp7ReturnTypeClass::class), $bind);
        $expected = 'function returnTypeArray() : array';
        $this->assertStringContainsString($expected, $code->code);
    }

    public function testInvalidSourceClass() : void
    {
        $this->expectException(InvalidSourceClassException::class);
        $this->codeGen->generate(new \ReflectionClass(\stdClass::class), new Bind);
    }
}
