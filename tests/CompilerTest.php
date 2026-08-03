<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;
use FakeGlobalEmptyNamespaced;
use FakeGlobalNamespaced;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;
use Ray\Aop\Annotation\FakeMarker3;
use Ray\Aop\Exception\NotWritableException;
use ReflectionClass;

use function array_shift;
use function assert;
use function class_exists;
use function file_get_contents;
use function is_array;
use function passthru;
use function serialize;
use function unserialize;

class CompilerTest extends TestCase
{
    private BindInterface $bind;
    private Compiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new Compiler(__DIR__ . '/tmp');
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $this->bind = (new Bind())->bind(FakeWeaved::class, [$pointcut]);
    }

    public function testNewInstanceCreatesWeavedObject(): FakeMock
    {
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $this->assertInstanceOf(FakeMock::class, $mock);

        return $mock;
    }

    public function testCompileReturnsSameClassForMultipleCalls(): void
    {
        $class1 = $this->compiler->compile(FakeMock::class, $this->bind);
        $class2 = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class1));
        $this->assertSame($class1, $class2);
        $class1File = (new ReflectionClass($class1))->getFileName();
        $class2File = (new ReflectionClass($class1))->getFileName();
        $this->assertSame($class1File, $class2File);
    }

    #[Depends('testNewInstanceCreatesWeavedObject')]
    public function testWeavedClassExtendsOriginalClass(object $class): void
    {
        $parent = (new ReflectionClass($class))->getParentClass();
        if (! ($parent instanceof ReflectionClass)) {
            return;
        }

        $this->assertSame(FakeMock::class, $parent->getName());
    }

    #[Depends('testNewInstanceCreatesWeavedObject')]
    public function testWeavedObjectAppliesInterceptor(FakeMock $weaved): void
    {
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    #[Depends('testNewInstanceCreatesWeavedObject')]
    public function testInterceptorIsAppliedConsistently(FakeMock $weaved): void
    {
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    #[Depends('testNewInstanceCreatesWeavedObject')]
    public function testInterceptorModifiesReturnValue(FakeMock $weaved): void
    {
        $num = new FakeNum();
        $num->value = 1;
        $result = $weaved->returnSame(1);
        $this->assertSame(2, $result);
    }

    /**
     * PSR-12 puts the body brace on its own line, which is what kept the codegen
     * regex honest. A source class that puts it on the declaration line used to
     * compile to a proxy that did not parse (CompilationFailedException).
     */
    public function testClassWithSameLineBodyBraceIsWeavedIntoWorkingProxy(): void
    {
        $bind = (new Bind())->bind(FakeSameLineBraceClass::class, [$this->doublePointcut()]);
        $weaved = $this->compiler->newInstance(FakeSameLineBraceClass::class, [], $bind);

        // The source interface survives alongside the weaved marker
        $this->assertInstanceOf(FakeNullInterface::class, $weaved);
        $this->assertInstanceOf(WeavedInterface::class, $weaved);
        $this->assertSame(2, $weaved->returnSame(1));
    }

    /** A multi-line implements list used to lose every name after the first. */
    public function testClassWithMultiLineImplementsIsWeavedIntoWorkingProxy(): void
    {
        $bind = (new Bind())->bind(FakeMultiLineImplementsClass::class, [$this->doublePointcut()]);
        $weaved = $this->compiler->newInstance(FakeMultiLineImplementsClass::class, [], $bind);

        $this->assertInstanceOf(FakeNullInterface::class, $weaved);
        $this->assertInstanceOf(FakeNullInterface1::class, $weaved);
        $this->assertInstanceOf(WeavedInterface::class, $weaved);
        $this->assertSame(2, $weaved->returnSame(1));
    }

    private function doublePointcut(): Pointcut
    {
        $matcher = new Matcher();

        return new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
    }

    public function testInheritedMethodsAreIntercepted(): void
    {
        $mock = $this->compiler->newInstance(FakeMockGrandChild::class, [], $this->bind);
        $result = $mock->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testTypedInheritedMethodsAreIntercepted(): void
    {
        $bind = (new Bind())->bindInterceptors('passIterator', [new NullInterceptor()]);
        $mock = $this->compiler->newInstance(FakeTypedMockGrandChild::class, [], $bind);
        $result = $mock->passIterator(new ArrayIterator());
        $this->assertInstanceOf(ArrayIterator::class, $result);
    }

    public function testGrandparentMethodsAreIntercepted(): void
    {
        $mock = $this->compiler->newInstance(FakeMockChildChild::class, [], $this->bind);
        $result = $mock->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testPrivatePropertyAccessIsPreserved(): void
    {
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $val = $mock->getPrivateVal();
        $this->assertSame(1, $val);
    }

    public function testAbortingInterceptorPreventsMethodExecution(): void
    {
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeAbortProceedInterceptor()]);
        $this->bind->bind(FakeWeaved::class, [$pointcut]);
        $mock = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $this->assertSame(40, $mock->returnSame(1));
        $this->assertSame(40, $mock->returnSame(1));
    }

    public function testClassDocCommentIsPreserved(): void
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $docComment = (string) (new ReflectionClass($weaved))->getDocComment();
        $expected = (new ReflectionClass(FakeMock::class))->getDocComment();
        $this->assertStringContainsString('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testMethodDocCommentIsPreserved(): void
    {
        $weaved = $this->compiler->newInstance(FakeMock::class, [], $this->bind);
        $docComment = (string) (new ReflectionClass($weaved))->getMethods()[0]->getDocComment();
        $expected = (new ReflectionClass(FakeMock::class))->getMethods()[0]->getDocComment();

        $this->assertStringContainsString('/**', $docComment);
        $this->assertSame($expected, $docComment);
    }

    public function testClassWithoutDocCommentHandledCorrectly(): void
    {
        $weaved = $this->compiler->newInstance(FakeMockNoDoc::class, [], $this->bind);
        $classDocComment = (new ReflectionClass($weaved))->getDocComment();
        $methodDocComment = (new ReflectionClass($weaved))->getMethods()[0]->getDocComment();

        $this->assertFalse((bool) $classDocComment);
        $this->assertFalse((bool) $methodDocComment);
    }

    public function testCompilerCanBeSerialized(): void
    {
        $compiler = unserialize(serialize($this->compiler));
        $this->assertInstanceOf(Compiler::class, $compiler);
        $class = $compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));
    }

    public function testCompiledFileCanBeIncluded(): void
    {
        passthru('php ' . __DIR__ . '/script/compile.php');
        /** @var class-string $mock */
        $mock = require __DIR__ . '/script/compile.php';
        $isWeaved = (new ReflectionClass($mock))->implementsInterface(WeavedInterface::class);
        $this->assertTrue($isWeaved);
    }

    public function testCompileWithoutBindingsReturnsOriginalClass(): void
    {
        $class = $this->compiler->compile(FakeMock::class, new Bind());
        $this->assertSame(FakeMock::class, $class);
    }

    public function testAnnotationsArePreservedInCompiledClass(): void
    {
        $class = $this->compiler->compile(FakeAnnotateClass::class, $this->bind);
        /** @var object[] $annotations */
        $annotations = (new ReflectionMethod($class, 'getDouble'))->getAnnotations();
        $this->assertCount(4, $annotations);
    }

    public function testClassWithoutNamespaceCanBeCompiled(): void
    {
        $class = $this->compiler->compile(FakeAnnotateClassNoName::class, $this->bind);
        /** @var object[] $annotations */
        $annotations = (new ReflectionMethod($class, 'getDouble'))->getAnnotations();
        $this->assertCount(3, $annotations);
    }

    public function testArrayAndCallableTypeHintsArePreserved(): void
    {
        $class = $this->compiler->compile(FakeArrayTypehinted::class, $this->bind);
        $this->assertTrue(class_exists($class));
        $file = (string) file_get_contents((string) (new ReflectionClass($class))->getFileName());
        $expected = 'public function returnSame(array $arrayParam, callable $callableParam)';
        $this->assertStringContainsString($expected, $file);
    }

    public function testNonWritableDirectoryThrowsException(): void
    {
        $this->expectException(NotWritableException::class);

        new Compiler('./not_available');
    }

    public function testCompilerWithBindings(): void
    {
        $this->compiler = new Compiler(__DIR__ . '/tmp');
        $this->bind = new Bind();
        $matcher = new Matcher();
        $pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
        $this->bind->bind(FakeMock::class, [$pointcut]);
        $class = $this->compiler->compile(FakeMock::class, $this->bind);
        $this->assertTrue(class_exists($class));
    }

    public function testMethodAnnotationReaderInInterceptor(): void
    {
        $bind = (new Bind())->bindInterceptors('getDouble', [new FakeMethodAnnotationReaderInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeAnnotateClass::class, [], $bind);
        $this->assertInstanceOf(FakeAnnotateClass::class, $mock);
        $mock->getDouble(1);
        $methodAnnotation = FakeMethodAnnotationReaderInterceptor::$methodAnnotation;
        $this->assertInstanceOf(FakeMarker::class, $methodAnnotation);
        /** @var object[] $methodAnnotations */
        $methodAnnotations = FakeMethodAnnotationReaderInterceptor::$methodAnnotations;
        $this->assertCount(4, $methodAnnotations);
        $annotation = array_shift($methodAnnotations);
        $this->assertInstanceOf(FakeMarker3::class, $annotation);
    }

    #[Depends('testMethodAnnotationReaderInInterceptor')]
    public function testClassAnnotationReaderInInterceptor(): void
    {
        $classAnnotation = FakeMethodAnnotationReaderInterceptor::$classAnnotation;
        $classAnnotations = FakeMethodAnnotationReaderInterceptor::$classAnnotations;
        $this->assertInstanceOf(FakeClassAnnotation::class, $classAnnotation);
        /** @var object[] $classAnnotations */
        $this->assertCount(2, $classAnnotations);
        $annotation = array_shift($classAnnotations);
        $this->assertInstanceOf(FakeResource::class, $annotation);
    }

    public function testMethodWithoutAnnotationReturnsNull(): void
    {
        $bind = (new Bind())->bindInterceptors('returnSame', [new FakeMethodAnnotationReaderInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeMock::class, [], $bind);
        $this->assertInstanceOf(FakeMock::class, $mock);
        $mock->returnSame(1);
        $this->assertNull(FakeMethodAnnotationReaderInterceptor::$methodAnnotation);
        assert(is_array(FakeMethodAnnotationReaderInterceptor::$methodAnnotations));
        $this->assertCount(0, FakeMethodAnnotationReaderInterceptor::$methodAnnotations);
    }

    public function testInterceptorCanModifyArguments(): void
    {
        $bind = (new Bind())->bindInterceptors('returnSame', [new FakeChangeArgsInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakeMock::class, [], $bind);
        $this->assertInstanceOf(FakeMock::class, $mock);
        $mock->returnSame(1);
        $this->assertSame('changed', $mock->returnSame(1));
    }

    public function testGlobalNamespacedClassCanBeWeaved(): void
    {
        $mock = $this->compiler->newInstance(FakeGlobalNamespaced::class, [], $this->bind);
        $this->assertInstanceOf(FakeGlobalNamespaced::class, $mock);
        $this->assertSame(2, $mock->returnSame(1));
    }

    public function testEmptyNamespaceClassCanBeWeaved(): void
    {
        $mock = $this->compiler->newInstance(FakeGlobalEmptyNamespaced::class, [], $this->bind);
        $this->assertInstanceOf(FakeGlobalEmptyNamespaced::class, $mock);
        $this->assertSame(2, $mock->returnSame(1));
    }

    public function testVoidReturnTypeIsPreserved(): void
    {
        $bind = (new Bind())->bindInterceptors('returnTypeVoid', [new FakeChangeArgsInterceptor()]);
        $compiler = new Compiler(__DIR__ . '/tmp');
        $mock = $compiler->newInstance(FakePhp71NullableClass::class, [], $bind);
        $this->assertInstanceOf(FakePhp71NullableClass::class, $mock);
        $mock->returnTypeVoid();
        $this->assertTrue($mock->returnTypeVoidCalled);
    }

    public function testAnonymousClassCanBeWeaved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('hasAnonymousClass', [new NullInterceptor()]);
        $mock = $this->compiler->newInstance(FakeAnonymousClass::class, [], $bind);
        $this->assertInstanceOf(FakeAnonymousClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }

    public function testMixedTypeParameterIsHandled(): void
    {
        $mock = $this->compiler->newInstance(FakeMixedParamClass::class, [], $this->bind);
        $this->assertInstanceOf(FakeMixedParamClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }

    public function testReadOnlyClassCanBeWeaved(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('greet', [new NullInterceptor()]);
        $mock = $this->compiler->newInstance(FakePhp82ReadOnlyClass::class, [], $bind);
        $this->assertInstanceOf(FakePhp82ReadOnlyClass::class, $mock);
        $this->assertInstanceOf(WeavedInterface::class, $mock);
    }

    public function testReadOnlyClassMethodInterception(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('greet', [new NullInterceptor()]);
        $mock = $this->compiler->newInstance(FakePhp82ReadOnlyClass::class, [], $bind);
        // Invoke intercepted method — validates codegen + trait compatibility
        $result = $mock->greet('World');
        $this->assertSame('Hello, World', $result);
    }

    public function testCompileWithBindingForExistingMethod(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnSame', [new FakeDoubleInterceptor()]);
        $class = $this->compiler->compile(FakeMock::class, $bind);

        // Should compile and create weaved class
        $this->assertNotSame(FakeMock::class, $class);
        $this->assertTrue(class_exists($class));
    }

    public function testCompileWithBindingForNonExistingMethod(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('nonExistentMethod', [new FakeDoubleInterceptor()]);
        $class = $this->compiler->compile(FakeMock::class, $bind);

        // Bindings that match no methods on the class are a no-op — return original FQN
        $this->assertSame(FakeMock::class, $class);
    }

    public function testCompileWithMixedExistingAndNonExistingMethods(): void
    {
        $bind = new Bind();
        $bind->bindInterceptors('returnSame', [new FakeDoubleInterceptor()]);
        $bind->bindInterceptors('nonExistentMethod', [new FakeDoubleInterceptor()]);
        $class = $this->compiler->compile(FakeMock::class, $bind);

        // Should compile because at least one method exists
        $this->assertNotSame(FakeMock::class, $class);
        $this->assertTrue(class_exists($class));
    }

    /**
     * Regression: interceptor calling another intercepted method on the same object
     * must not cause infinite recursion (old _isAspect flag was prone to this)
     */
    public function testReentrantInterceptorCrossMethodCall(): void
    {
        $bind = new Bind();
        $interceptor = new FakeReentrantInterceptor();
        $bind->bindInterceptors('returnSame', [$interceptor]);
        $bind->bindInterceptors('getSub', [$interceptor]);

        $mock = $this->compiler->newInstance(FakeMock::class, [], $bind);
        $result = $mock->returnSame(42);

        $this->assertSame(42, $result);
    }
}
