<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use stdClass;

use function class_exists;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class AopCodeGenTest extends TestCase
{
    /** @var AopCodeGen */
    private $codeGen;

    protected function setUp(): void
    {
        $this->codeGen = new AopCodeGen();
    }

    public function testTypeDeclarations(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7Class::class), $bind);
        $expected = 'function run(string $a, int $b, float $c, bool $d): array';
        $this->assertStringContainsString($expected, $code);
    }

    public function testReturnType(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnTypeArray', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7ReturnTypeClass::class), $bind);
        $expected = 'function returnTypeArray(): array';
        $this->assertStringContainsString($expected, $code);
    }

    /** @requires PHP 8.1 */
    public function testUnionType(): void
    {
        $bind = new Bind();
        for ($i = 1; $i <= 20; $i++) {
            $bind->bindInterceptors('method' . (string) $i, []);
        }

        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind);
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($tempFile, $code);
        require $tempFile;
        unlink($tempFile);
        $this->assertTrue(class_exists('\Ray\Aop\FakePhp8Types_aop'));

        $this->assertStringContainsString('public function method1($param1)', $code);
        $this->assertStringContainsString('public function method2(string $param1)', $code);
        $this->assertStringContainsString('public function method3(int $param1)', $code);
        $this->assertStringContainsString('public function method4(null|string $param1)', $code);
        $this->assertStringContainsString('public function method5(null|int $param1)', $code);
        $this->assertStringContainsString('public function method6(string $param1 = \'default\')', $code);
        $this->assertStringContainsString('public function method7(null|int $param1 = NULL)', $code);
        $this->assertStringContainsString('public function method8(&$param1)', $code);
        $this->assertStringContainsString('public function method9(array $param1)', $code);
        $this->assertStringContainsString('public function method10(null|array $param1)', $code);
        $this->assertStringContainsString('public function method11(...$params)', $code);
        $this->assertStringContainsString('public function method12(string|int $param1)', $code);
        $this->assertStringContainsString('public function method13(\\DateTime|string $param1)', $code);
        $this->assertStringContainsString('public function method14(string|int|null $param1)', $code);
        $this->assertStringContainsString('public function method15(\DateTime|string|null $param1)', $code);
        $this->assertStringContainsString('public function method16(): string', $code);
        $this->assertStringContainsString('public function method17(): \\DateTime', $code);
        $this->assertStringContainsString('public function method18(): string|int', $code);
        $this->assertStringContainsString('public function method19(): string|int|null', $code);
        $this->assertStringContainsString('public function method20(): \DateTime|string|null', $code);
    }

    public function testInvalidSourceClass(): void
    {
        $this->expectException(InvalidSourceClassException::class);
        $this->codeGen->generate(new ReflectionClass(stdClass::class), new Bind());
    }
}
