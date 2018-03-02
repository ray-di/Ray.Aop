<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
}
