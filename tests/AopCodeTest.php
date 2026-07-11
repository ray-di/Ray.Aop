<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use stdClass;

use function class_exists;
use function file_put_contents;
use function implode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const PHP_EOL;

class AopCodeTest extends TestCase
{
    private AopCode $codeGen;

    protected function setUp(): void
    {
        $this->codeGen = new AopCode(new MethodSignatureString());
    }

    public function testTypeDeclarationsArePreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7Class::class), $bind, '_test');
        $expected = 'function run(string $a, int $b, float $c, bool $d): array';
        $this->assertStringContainsString($expected, $code);
    }

    public function testReturnTypeIsPreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnTypeArray', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7ReturnTypeClass::class), $bind, '_test');
        $expected = 'function returnTypeArray(): array';
        $this->assertStringContainsString($expected, $code);
    }

    public function testVariousMethodSignaturesInPhp81(): void
    {
        $bind = new Bind();
        for ($i = 1; $i <= 26; $i++) {
            $bind->bindInterceptors('method' . (string) $i, []);
        }

        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($tempFile, $code);
        require $tempFile;
        unlink($tempFile);
        $this->assertTrue(class_exists('\Ray\Aop\FakePhp8Types_test'));

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

        // PHPDoc is not generated
        $phpDoc = '    /**
     * PHPDoc
     */';
        $this->assertStringContainsString(
            implode(
                PHP_EOL,
                [
                    $phpDoc,
                    '     #[\\Ray\\Aop\\Annotation\\FakeMarker4(array(0=>1,1=>2,), 3)]',
                    '      public function method21()',
                ]
            ),
            $code
        );
        $this->assertStringContainsString(
            implode(
                PHP_EOL,
                [
                    '     #[\\Ray\\Aop\\Annotation\\FakeMarkerName(a: 1, b: \'string\', c: true)]',
                    '      public function method22()',
                ]
            ),
            $code
        );
        $this->assertStringContainsString(
            implode(
                PHP_EOL,
                [
                    '     #[\\Ray\\Aop\\Annotation\\FakeMarker5(\\Ray\\Aop\\FakePhp81Enum::Apple)]',
                    '      public function method23()',
                ]
            ),
            $code
        );
        $this->assertStringContainsString(
            implode(
                PHP_EOL,
                [
                    '     #[\\Ray\\Aop\\Annotation\\FakeMarker6(fruit1: \\Ray\\Aop\\FakePhp81Enum::Apple, fruit2: \\Ray\\Aop\\FakePhp81Enum::Orange)]',
                    '      public function method24()',
                ]
            ),
            $code
        );
        $this->assertStringContainsString("public function method25(#[\Ray\Aop\Attribute\FakeAttr1()] \$a, #[\Ray\Aop\Attribute\FakeAttr1()] #[\Ray\Aop\Attribute\FakeAttr2(name: 'famicon', age: 40)] \$b): void", $code);
        // $1 in attribute args must survive codegen (preg_replace would strip it as a backreference)
        $this->assertStringContainsString('a$1b', $code);
    }

    public function testVariousMethodSignaturesInPhp82(): void
    {
        $bind = new Bind();
        for ($i = 100; $i <= 106; $i++) {
            $bind->bindInterceptors('method' . (string) $i, []);
        }

        $code = $this->codeGen->generate(new ReflectionClass(FakePhp82Types::class), $bind, '_test');
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($tempFile, $code);
        require $tempFile;
        unlink($tempFile);
        $this->assertTrue(class_exists('\Ray\Aop\FakePhp82Types_test'));
        $this->assertStringContainsString('public function method100(): false', $code);
        $this->assertStringContainsString('public function method101(): true', $code);
        $this->assertStringContainsString('public function method102(): null', $code);
        $this->assertStringContainsString('public function method103(): \Ray\Aop\FakeNullInterface & \Ray\Aop\FakeNullInterface1', $code);
        $this->assertStringContainsString('public function method104(): \Ray\Aop\FakeNullInterface|\Ray\Aop\FakeNullInterface1', $code);
        $this->assertStringContainsString('public function method105(): \Ray\Aop\FakeNullInterface|string', $code);
        $this->assertStringContainsString('public function method106(): (\Ray\Aop\FakeNullInterface&\Ray\Aop\FakeNullInterface1)|string', $code);
    }

    public function testGeneratingCodeForInvalidSourceClassThrowsException(): void
    {
        $this->expectException(InvalidSourceClassException::class);
        $this->codeGen->generate(new ReflectionClass(stdClass::class), new Bind(), '_test');
    }

    public function testVoidReturnTypeMethodDoesNotHaveReturnStatement(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnTypeVoid', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp71NullableClass::class), $bind, '_test');

        // void return type should not have 'return' before intercept statement
        $this->assertStringContainsString('function returnTypeVoid(): void', $code);
        $this->assertStringNotContainsString('return $invocation->proceed', $code);
        $this->assertStringContainsString('$invocation->proceed();', $code);
    }

    public function testNonVoidReturnTypeMethodHasReturnStatement(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnNullable', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp71NullableClass::class), $bind, '_test');

        // non-void return type should have 'return'
        $this->assertStringContainsString('function returnNullable(string $str): null|int', $code);
        $this->assertStringContainsString('return $invocation->proceed();', $code);
        // Closing brace must not glue onto proceed()
        $this->assertStringNotContainsString('proceed();    }', $code);
        $this->assertMatchesRegularExpression('/\$invocation->proceed\(\);\n    \}/', $code);
    }

    public function testClassWithExistingImplementsGetsWeavedInterfaceAdded(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method1', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');

        // Class already has implements, should add WeavedInterface to existing list
        $this->assertStringContainsString('implements FakeNullInterface, \Ray\Aop\FakeNullInterface1, \Ray\Aop\WeavedInterface', $code);
    }

    public function testClassWithoutImplementsGetsWeavedInterfaceAdded(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7Class::class), $bind, '_test');

        // Class without implements should get WeavedInterface added
        $this->assertStringContainsString('implements \Ray\Aop\WeavedInterface', $code);
    }

    public function testGeneratedCodeHasCorrectClassDeclaration(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('run', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7Class::class), $bind, '_test');

        // The class declaration should have proper extends syntax
        $this->assertStringContainsString('class FakePhp7Class_test extends FakePhp7Class', $code);
    }

    public function testUnionReturnTypeMethodHasReturnStatement(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method18', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');

        // union return type should have 'return'
        $this->assertStringContainsString('function method18(): string|int', $code);
        $this->assertStringContainsString('return $invocation->proceed();', $code);
    }

    public function testEmptyBindingsDoesNotAddMethods(): void
    {
        $bind = new Bind();
        // No bindings
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp7Class::class), $bind, '_test');

        // Should still have the class but no intercepted methods
        $this->assertStringContainsString('class FakePhp7Class_test extends FakePhp7Class', $code);
        $this->assertStringNotContainsString('ReflectiveMethodInvocation', $code);
        // Early-return in addMethods must skip insert(''): empty insert truncates the
        // trailing newline after the final brace (ReturnRemoval mutant).
        $this->assertStringEndsWith("}\n", $code);
    }

    public function testIntersectionTypeReturnIsPreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method103', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp82Types::class), $bind, '_test');

        // intersection type should be preserved
        $this->assertStringContainsString('\Ray\Aop\FakeNullInterface & \Ray\Aop\FakeNullInterface1', $code);
        $this->assertStringContainsString('return $invocation->proceed();', $code);
    }

    public function testDnfTypeReturnIsPreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method106', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp82Types::class), $bind, '_test');

        // DNF type (intersection inside union) should be preserved
        $this->assertStringContainsString('(\Ray\Aop\FakeNullInterface&\Ray\Aop\FakeNullInterface1)|string', $code);
        $this->assertStringContainsString('return $invocation->proceed();', $code);
    }

    public function testEnumAttributeArgumentIsPreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method23', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');

        // Enum value as attribute argument should be preserved
        $this->assertStringContainsString('#[\Ray\Aop\Annotation\FakeMarker5(', $code);
        $this->assertStringContainsString('FakePhp81Enum::Apple', $code);
    }

    public function testNamedEnumAttributeArgumentsArePreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method24', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');

        // Named Enum arguments should be preserved
        $this->assertStringContainsString('#[\Ray\Aop\Annotation\FakeMarker6(', $code);
        $this->assertStringContainsString('fruit1:', $code);
        $this->assertStringContainsString('fruit2:', $code);
    }

    public function testParameterAttributesArePreserved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('method25', []);
        $code = $this->codeGen->generate(new ReflectionClass(FakePhp8Types::class), $bind, '_test');

        // Parameter attributes should be preserved (format: #[\Class\Name()])
        $this->assertStringContainsString('#[\Ray\Aop\Attribute\FakeAttr1()]', $code);
        $this->assertStringContainsString('#[\Ray\Aop\Attribute\FakeAttr2(name:', $code);
        $this->assertStringContainsString('age: 40', $code);
    }
}
