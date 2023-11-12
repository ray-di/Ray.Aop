<?php

declare(strict_types=1);

namespace Ray\Aop;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

use function array_keys;
use function implode;
use function in_array;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;

final class GeneratedCode
{
    public const INTERCEPT_STATEMENT = '\$this->_intercept(__FUNCTION__, func_get_args());';

    /** @var string */
    private $code = '';

    /** @var int  */
    private $curlyBraceCount = 0;

    /** @var MethodSignatureString */
    private $methodSignature;

    public function __construct(MethodSignatureString $methodSignature)
    {
        $this->methodSignature = $methodSignature;
    }

    /** @return void */
    public function add(string $text)
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

    /** @param  non-empty-string $code */
    public function insert(string $code): void
    {
        $replacement = $code . '}';
        $this->code = (string) preg_replace('/}\s*$/', $replacement, $this->code);
    }

    public function addClassName(string $className, string $postfix): void
    {
        $newClassName = $className . $postfix;
        $this->add($newClassName . ' extends ' . $className . ' ');
    }

    public function implementsInterface(string $interfaceName): void
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
    public function addMethods(ReflectionClass $class, BindInterface $bind): void
    {
        $bindings = array_keys($bind->getBindings());

        $parentMethods = $class->getMethods();
        $interceptedMethods = [];
        foreach ($parentMethods as $method) {
            if (! in_array($method->getName(), $bindings)) {
                continue;
            }

            $signature = $this->methodSignature->get($method);
            $isVoid = false;
            if ($method->hasReturnType() && (! $method->getReturnType() instanceof ReflectionUnionType)) {
                $nt = $method->getReturnType();
                $isVoid = $nt instanceof ReflectionNamedType && $nt->getName()  === 'void';
            }

            $return = $isVoid ? '' : 'return ';
            $interceptedMethods[] = sprintf("    %s\n    {\n        %s%s\n    }\n", $signature, $return, self::INTERCEPT_STATEMENT);
        }

        if (! $interceptedMethods) {
            return;
        }

        $this->insert(implode("\n", $interceptedMethods));
    }

    public function addIntercepterTrait(): void
    {
        $this->add(sprintf("{\n    use \%s;\n}\n", InterceptTrait::class));
    }

    public function getCodeText(): string
    {
        // close opened curly brace
        while ($this->curlyBraceCount !== 0) {
            $this->code .= '}';
            $this->curlyBraceCount--;
        }

        return $this->code;
    }
}
