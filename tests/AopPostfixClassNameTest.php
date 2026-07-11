<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function crc32;
use function filemtime;
use function substr;

class AopPostfixClassNameTest extends TestCase
{
    public function testPostfixStartsWithUnderscore(): void
    {
        $className = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp');
        $this->assertStringStartsWith('_', $className->postFix);
    }

    /**
     * Pins the exact hash inputs (order + GENERATION). Concat reorder / GENERATION
     * removal mutants change crc32 and must fail here.
     */
    public function testPostfixMatchesDeterministicHashFormula(): void
    {
        $bindings = 'bindings';
        $classDir = '/tmp';
        $fileTime = (string) filemtime((string) (new ReflectionClass(FakeClass::class))->getFileName());
        $expected = '_' . crc32($fileTime . $bindings . $classDir . AopCode::GENERATION);

        $className = new AopPostfixClassName(FakeClass::class, $bindings, $classDir);

        $this->assertSame($expected, $className->postFix);
        $this->assertSame(FakeClass::class . $expected, $className->fqn);
    }

    public function testFqnContainsOriginalClassNameAndPostfix(): void
    {
        $className = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp');
        $this->assertStringStartsWith(FakeClass::class, $className->fqn);
        $this->assertNotEmpty($className->postFix);
        $this->assertSame(FakeClass::class . $className->postFix, $className->fqn);
    }

    public function testDifferentBindingsProduceDifferentPostfixes(): void
    {
        $className1 = new AopPostfixClassName(FakeClass::class, 'bindings1', '/tmp');
        $className2 = new AopPostfixClassName(FakeClass::class, 'bindings2', '/tmp');

        $this->assertNotEquals($className1->postFix, $className2->postFix);
    }

    public function testDifferentClassDirProducesDifferentPostfixes(): void
    {
        $className1 = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp1');
        $className2 = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp2');

        $this->assertNotEquals($className1->postFix, $className2->postFix);
    }

    public function testSameInputsProduceSamePostfix(): void
    {
        $className1 = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp');
        $className2 = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp');

        $this->assertEquals($className1->postFix, $className2->postFix);
        $this->assertEquals($className1->fqn, $className2->fqn);
    }

    public function testPostfixIsNumeric(): void
    {
        $className = new AopPostfixClassName(FakeClass::class, 'bindings', '/tmp');
        // postFix format is '_' followed by crc32 hash (numeric)
        $numericPart = substr($className->postFix, 1);
        $this->assertMatchesRegularExpression('/^-?\d+$/', $numericPart);
    }

    public function testEmptyBindingsProducesValidPostfix(): void
    {
        $className = new AopPostfixClassName(FakeClass::class, '', '/tmp');
        $this->assertStringStartsWith('_', $className->postFix);
        $this->assertNotEmpty($className->postFix);
    }

    public function testBindingsOrderMatters(): void
    {
        // This tests that changing the order of concatenation would produce different results
        $className1 = new AopPostfixClassName(FakeClass::class, 'abc', '/xyz');
        $className2 = new AopPostfixClassName(FakeClass::class, 'xyz', '/abc');

        $this->assertNotEquals($className1->postFix, $className2->postFix);
    }
}
