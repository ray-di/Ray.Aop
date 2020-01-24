<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

class CodeGenPhp71Test extends TestCase
{
    /**
     * @var CodeGen
     */
    private $codeGen;

    protected function setUp() : void
    {
        $this->codeGen = new CodeGen((new ParserFactory)->newInstance(), new BuilderFactory, new Standard);
    }

    public function testReturnTypeVoid()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnTypeVoid', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $expected = 'function returnTypeVoid() : void';
        $this->assertStringContainsString($expected, $code->code);
    }

    public function testReturnTypeVoidContainsNoReturnValue()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnTypeVoid', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $this->assertStringNotContainsString('return ', $code->code);
        $this->assertStringContainsString('return;', $code->code);
    }

    public function testReturnTypeNullable()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnNullable', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $expected = 'function returnNullable(string $str) : ?';
        $this->assertStringContainsString($expected, $code->code);

        return $code;
    }

    /**
     * @depends testReturnTypeNullable
     */
    public function testContainsStatement(Code $code)
    {
        $this->assertStringContainsString("declare (strict_types=1);\n", $code->code);
        $this->assertStringContainsString("use Composer\\Autoload;\n", $code->code);
    }

    public function testNullableParam()
    {
        $bind = new Bind;
        $bind->bindInterceptors('nullableParam', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $expected = 'function nullableParam(?int $id, string $name = null)';
        $this->assertStringContainsString($expected, $code->code);
    }

    public function testTypedParam()
    {
        $bind = new Bind;
        $bind->bindInterceptors('typed', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $expected = 'public function typed(\SplObjectStorage $storage)';
        $this->assertStringContainsString($expected, $code->code);
    }

    public function testUseTyped()
    {
        $bind = new Bind;
        $bind->bindInterceptors('useTyped', []);
        $code = $this->codeGen->generate(new \ReflectionClass(FakePhp71NullableClass::class), $bind);
        $expected = 'public function useTyped(CodeGen $codeGen)';
        $this->assertStringContainsString($expected, $code->code);
    }
}
