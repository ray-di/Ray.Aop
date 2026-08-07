<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

use function file_get_contents;
use function implode;
use function is_object;
use function preg_replace;
use function preg_replace_callback;
use function rtrim;
use function sprintf;
use function strrpos;
use function substr;
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
    public const GENERATION = 7;

    /**
     * Template for direct parent-FCC dispatch (no _intercept, no _isAspect flag).
     * Leading // line lists interceptor short class names (self-documenting weaved code).
     * Two statements: build MethodInvocation, then proceed (compact, no blank line).
     */
    // Closing delimiter at column 0 so body keeps 8-space method indent (PSR12)
    private const INVOKE_TEMPLATE = <<<'PHP'
        // %s
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation($this, '%s', func_get_args(), $this->bindings['%s'], parent::%s(...));
        %s$invocation->proceed();
PHP;

    /** Template for readonly classes (bindings accessed via $_state) */
    private const INVOKE_READONLY_TEMPLATE = <<<'PHP'
        // %s
        $invocation = new \Ray\Aop\ReflectiveMethodInvocation($this, '%s', func_get_args(), $this->_state->bindings['%s'], parent::%s(...));
        %s$invocation->proceed();
PHP;

    private const CLASS_DECLARATION_PATTERN = 'class\s+[\w\s]+extends\s+\w+';

    /** Matches the implements list, ending before the body brace; [^{]*[^{\s] stops at the last interface name */
    private const IMPLEMENTS_LIST_PATTERN = '[^{]*[^{\s]';

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
        $this->code = '';
        $this->curlyBraceCount = 0;
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
            return; // @codeCoverageIgnore
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
                // Drop the source whitespace before the body brace; the trait template
                // re-adds it as a newline so the brace owns its line whatever the
                // source style was (PSR2 SpaceBeforeBrace / EndLine).
                $this->code = rtrim($this->code);
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
        $pattern = '/(' . self::CLASS_DECLARATION_PATTERN . ')(?:\s+implements\s+(' . self::IMPLEMENTS_LIST_PATTERN . '))?/';
        $this->code = (string) preg_replace_callback($pattern, static function ($matches) use ($interfaceName) {
            if (isset($matches[2])) {
                // A multi-line list is folded onto the declaration line
                $interfaces = (string) preg_replace('/\s+/', ' ', $matches[2]);

                return sprintf('%s implements %s, \\%s', rtrim($matches[1]), $interfaces, $interfaceName);
            }

            return sprintf('%s implements \\%s', rtrim($matches[0]), $interfaceName);
        }, $this->code);
        // Class declaration must not end with trailing spaces (before body brace)
        $this->code = (string) preg_replace('/[ \t]+$/m', '', $this->code);
    }

    /** @param ReflectionClass<object> $class */
    private function addMethods(ReflectionClass $class, BindInterface $bind, bool $isReadOnly): void
    {
        $bindings = $bind->getBindings();
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
            /** @var list<object|class-string> $interceptors */
            $interceptors = $bindings[$methodName];
            $body = sprintf(
                $template,
                $this->interceptorShortNames($interceptors),
                $methodName, // '(string) method' arg
                $methodName, // bindings key
                $methodName, // parent::method(...)
                $return,     // 'return ' or ''
            );
            // Signature is already 4-space indented; body uses 8-space indent (PSR12)
            $interceptedMethods[] = sprintf("%s\n    {\n%s\n    }\n", $signature, rtrim($body, "\n"));
        }

        if (! $interceptedMethods) {
            return;
        }

        $this->insert(implode("\n", $interceptedMethods));
    }

    /**
     * Short class names for the always-on weaved-method comment (self-documenting bind).
     *
     * @param list<object|class-string> $interceptors
     */
    private function interceptorShortNames(array $interceptors): string
    {
        $names = [];
        foreach ($interceptors as $interceptor) {
            $fqn = is_object($interceptor) ? $interceptor::class : $interceptor;
            $pos = strrpos($fqn, '\\');
            $names[] = $pos === false ? $fqn : substr($fqn, $pos + 1);
        }

        return $names === [] ? '(none)' : implode(', ', $names);
    }

    /** @psalm-external-mutation-free */
    private function addInterceptorTrait(): void
    {
        // Blank line after trait use (PSR12.Traits.UseDeclaration)
        $this->add(sprintf("\n{\n    use \\%s;\n\n}\n", InterceptTrait::class));
    }

    /** @psalm-external-mutation-free */
    private function addReadOnlyInterceptorTrait(): void
    {
        $this->add(sprintf("\n{\n    use \\%s;\n\n}\n", ReadOnlyInterceptTrait::class));
    }

    /** @psalm-external-mutation-free */
    private function getCodeText(): string
    {
        // close opened curly brace
        while ($this->curlyBraceCount !== 0) {
            $this->code .= '}';
            $this->curlyBraceCount--;
        }

        // PSR2: single trailing newline at EOF
        return rtrim($this->code, "\n") . "\n";
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
