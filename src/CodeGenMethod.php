<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeAbstract;
use PhpParser\Parser;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use ReflectionMethod;

use function array_keys;
use function assert;
use function in_array;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
final class CodeGenMethod
{
    /** @var Parser */
    private $parser;

    /** @var VisitorFactory */
    private $visitorFactory;

    public function __construct(
        Parser $parser
    ) {
        $this->parser = $parser;
        $this->visitorFactory = new VisitorFactory($parser);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return ClassMethod[]
     */
    public function getMethods(ReflectionClass $reflectionClass, BindInterface $bind, CodeVisitor $code): array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $isBindingMethod = in_array($methodName, $bindingMethods, true);
            if ($isBindingMethod) {
                $classMethod = $this->getClassMethod($reflectionClass, $reflectionMethod, $code);
                $methodInsideStatements = $this->getTemplateMethodNodeStmts(
                    $classMethod->getReturnType()
                );
                // replace statements in the method
                $classMethod->stmts = $methodInsideStatements;
                $methods[] = $classMethod;
            }
        }

        return $methods;
    }

    /**
     * @return Stmt[]
     */
    private function getTemplateMethodNodeStmts(?NodeAbstract $returnType): array
    {
        $code = $this->isReturnVoid($returnType) ? AopTemplate::RETURN_VOID : AopTemplate::RETURN;
        $parts = $this->parser->parse($code);
        assert(isset($parts[0]));
        $node = $parts[0];
        assert($node instanceof Class_);
        $methodNode = $node->getMethods()[0];
        assert($methodNode->stmts !== null);

        return $methodNode->stmts;
    }

    private function isReturnVoid(?NodeAbstract $returnType): bool
    {
        return $returnType instanceof Identifier && $returnType->name === 'void';
    }

    /** @param ReflectionClass<object> $sourceClass */
    private function getClassMethod(
        ReflectionClass $sourceClass,
        ReflectionMethod $bindingMethod,
        CodeVisitor $code
    ): ClassMethod {
        $bindingMethodName = $bindingMethod->getName();
        foreach ($code->classMethod as $classMethod) {
            if ($classMethod->name->name === $bindingMethodName) {
                return $classMethod;
            }
        }

        $parentClass = $sourceClass->getParentClass();
        if ($parentClass === false) {
            throw new InvalidSourceClassException($sourceClass->getName()); // @codeCoverageIgnore
        }

        $code = ($this->visitorFactory)($parentClass);

        return $this->getClassMethod($parentClass, $bindingMethod, $code);
    }
}
