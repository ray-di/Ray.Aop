<?php
namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

class CodeGenTest extends TestCase
{
    /**
     * @var CodeGen
     */
    private $codeGen;

    public function setUp()
    {
        $this->codeGen = new CodeGen((new ParserFactory)->newInstance(), new BuilderFactory, new Standard);
    }

    public function testAddNullDefaultWithAssisted()
    {
        $bind = new Bind;
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate('a', new \ReflectionClass(FakeAssistedConsumer::class), $bind);
        $expected = 'function run($a, $b = null, $c = null)';
        $this->assertContains($expected, $code);
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

    public function testReturnTypeVoid()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnTypeVoid', []);
        $code = $this->codeGen->generate('a', new \ReflectionClass(FakePhp71ReturnTypeClass::class), $bind);
        $expected = 'function returnTypeVoid() : void';
        $this->assertContains($expected, $code);
    }

    public function testReturnTypeNullable()
    {
        $bind = new Bind;
        $bind->bindInterceptors('returnNullable', []);
        $code = $this->codeGen->generate('a', new \ReflectionClass(FakePhp71ReturnTypeClass::class), $bind);
        $expected = 'function returnNullable(string $str) : ?';
        $this->assertContains($expected, $code);

        return $code;
    }

    /**
     * @depends testReturnTypeNullable
     */
    public function testContainsStatement(string $code)
    {
        $this->assertContains("declare (strict_types=1);\n", $code);
        $this->assertContains("use Composer\Autoload;\n", $code);
    }
}
