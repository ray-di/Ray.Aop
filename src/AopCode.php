<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

use function array_flip;
use function array_keys;
use function file_get_contents;
use function implode;
use function preg_replace_callback;
use function sprintf;
use function strrpos;
use function substr_replace;
use function token_get_all;

use const T_CLASS;
use const T_EXTENDS;
use const T_STRING;

/**
 * Aop Code
 *
 * AopCode is responsible for generating code for Aspect-Oriented Programming (AOP)
 * by adding interceptors and modifying class definitions.
 */
final class AopCode
{
    /** Code generation version — bump on codegen changes to invalidate cached proxies */
    public const GENERATION = 2;

    /** Template for direct parent-FCC dispatch (no _intercept, no _isAspect flag) */
    private const INVOKE_TEMPLATE = <<<'PHP'
            $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, '%s', func_get_args(), $this->bindings['%s'], parent::%s(...));
            %s$__aop->proceed();
    PHP;

    /** Template for readonly classes (bindings accessed via $_state) */
    private const INVOKE_READONLY_TEMPLATE = <<<'PHP'
            $__aop = new \Ray\Aop\ReflectiveMethodInvocation($this, '%s', func_get_args(), $this->_state->bindings['%s'], parent::%s(...));
            %s$__aop->proceed();
    PHP;

    private string $code = '';
    private int $curlyBraceCount = 0;

    public function __construct(private readonly MethodSignatureString $methodSignature)
    {
    }

    /**
     * @param ReflectionClass<object> $sourceClass
     *
     * @psalm-external-mutation-free
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind, string $postfix): string
    {
        $this->parseClass($sourceClass, $postfix);
        $this->implementsInterface(WeavedInterface::class);
        $this->addMethods($sourceClass, $bind, $sourceClass->isReadOnly());

        return $this->getCodeText();
    }

    /**
     * @return void
     *
     * @psalm-external-mutation-free
     */
    private function add(string $text)
    {
        if ($text === '{') {
            $this->curlyBraceCount++;
        }

        if ($text === '}') {
            // @codeCoverageIgnoreStart
            $this->curlyBraceCount--;
            // @codeCoverageIgnoreEnd
        }

        $this->code .= $text;
    }

    /**
     * @param non-empty-string $code
     *
     * @psalm-external-mutation-free
     */
    private function insert(string $code): void
    {
        $lastBrace = strrpos($this->code, '}');
        if ($lastBrace === false) {
            return;
        }

        $this->code = substr_replace($this->code, $code . '}', $lastBrace);
    }

    /** @psalm-external-mutation-free */
    private function addClassName(string $className, string $postfix): void
    {
        $newClassName = $className . $postfix;
        $this->add($newClassName . ' extends ' . $className . ' ');
    }

    /** @param ReflectionClass<object> $sourceClass */
    private function getSourceCode(ReflectionClass $sourceClass): string
    {
        $fileName = $sourceClass->getFileName();
        if ($fileName === false) {
            throw new InvalidSourceClassException($sourceClass->getName());
        }

        $code = file_get_contents($fileName);
        if ($code === false) {
            throw new InvalidSourceClassException($sourceClass->getName()); // @codeCoverageIgnore
        }

        return $code;
    }

    /** @param ReflectionClass<object> $sourceClass */
    private function parseClass(ReflectionClass $sourceClass, string $postfix): void
    {
        $code = $this->getSourceCode($sourceClass);
        /** @var array<int, array{int, string, int}|string> $tokens */
        $tokens = token_get_all($code);
        $iterator = new TokenIterator($tokens);
        $inClass = false;
        $className = '';

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            [$id, $text] = $iterator->getToken();
            $isClassKeyword = $id === T_CLASS;
            if ($isClassKeyword) {
                $inClass = true;
                $this->add($text);
                continue;
            }

            $isClassName = $inClass && $id === T_STRING && empty($className);
            if ($isClassName) {
                $className = $text;
                $this->addClassName($className, $postfix);
                continue;
            }

            $isExtendsKeyword = $id === T_EXTENDS;
            if ($isExtendsKeyword) {
                $iterator->skipExtends();
                continue;
            }

            $isClassSignatureEnds = $inClass && $text === '{';
            if ($isClassSignatureEnds) {
                $this->resolveInterceptTrait($sourceClass);

                return;
            }

            $this->add($text);
        }
    }

    /**
     * @param interface-string $interfaceName
     *
     * @psalm-external-mutation-free
     */
    private function implementsInterface(string $interfaceName): void
    {
        $pattern = '/(class\s+[\w\s]+extends\s+\w+)(?:\s+implements\s+(.+))?/';
        $this->code = (string) preg_replace_callback($pattern, static function ($matches) use ($interfaceName) {
            if (isset($matches[2])) {
                // 既に implements が存在する場合
                // $match[0] class  FakePhp8Types_test extends FakePhp8Types  implements FakeNullInterface, \Ray\Aop\FakeNullInterface1
                // $match[1] class  FakePhp8Types_test extends FakePhp8Types
                // $match[2] FakeNullInterface, \Ray\Aop\FakeNullInterface1
                return sprintf('%s implements %s, \%s', $matches[1], $matches[2], $interfaceName);
            }

            // implements が存在しない場合
            return sprintf('%s implements \%s', $matches[0], $interfaceName);
        }, $this->code);
    }

    /** @param ReflectionClass<object> $class */
    private function addMethods(ReflectionClass $class, BindInterface $bind, bool $isReadOnly): void
    {
        $bindings = array_flip(array_keys($bind->getBindings()));
        $template = $isReadOnly ? self::INVOKE_READONLY_TEMPLATE : self::INVOKE_TEMPLATE;

        $parentMethods = $class->getMethods();
        $interceptedMethods = [];
        foreach ($parentMethods as $method) {
            $methodName = $method->getName();
            if (! isset($bindings[$methodName])) {
                continue;
            }

            $signature = $this->methodSignature->get($method);
            $isVoid = false;
            if ($method->hasReturnType() && (! $method->getReturnType() instanceof ReflectionUnionType)) {
                $nt = $method->getReturnType();
                $isVoid = $nt instanceof ReflectionNamedType && $nt->getName()  === 'void';
            }

            $return = $isVoid ? '' : 'return ';
            $body = sprintf(
                $template,
                $methodName, // '(string) method' arg
                $methodName, // bindings key
                $methodName, // parent::method(...)
                $return,     // 'return ' or ''
            );
            $interceptedMethods[] = sprintf("    %s\n    {\n%s    }\n", $signature, $body);
        }

        if (! $interceptedMethods) {
            return;
        }

        $this->insert(implode("\n", $interceptedMethods));
    }

    /** @psalm-external-mutation-free */
    private function addInterceptorTrait(): void
    {
        $this->add(sprintf("{\n    use \%s;\n}\n", InterceptTrait::class));
    }

    /** @psalm-external-mutation-free */
    private function addReadOnlyInterceptorTrait(): void
    {
        $this->add(sprintf("{\n    use \%s;\n}\n", ReadOnlyInterceptTrait::class));
    }

    /** @psalm-external-mutation-free */
    private function getCodeText(): string
    {
        // close opened curly brace
        while ($this->curlyBraceCount !== 0) {
            $this->code .= '}';
            $this->curlyBraceCount--;
        }

        return $this->code;
    }

    /** @param ReflectionClass<object> $sourceClass */
    private function resolveInterceptTrait(ReflectionClass $sourceClass): void
    {
        if ($sourceClass->isReadOnly()) {
            $this->addReadOnlyInterceptorTrait();

            return;
        }

        $this->addInterceptorTrait();
    }
}
