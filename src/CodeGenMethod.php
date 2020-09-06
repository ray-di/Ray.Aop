<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationException;
use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeAbstract;
use PhpParser\Parser;

use function array_keys;
use function assert;
use function in_array;

final class CodeGenMethod
{
    /** @var Parser */
    private $parser;

    /** @var BuilderFactory */
    private $factory;

    /**
     * @throws AnnotationException
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
    }

    /**
     * @return ClassMethod[]
     */
    public function getMethods(BindInterface $bind, CodeVisitor $code): array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $classMethods = $code->classMethod;
        $methods = [];
        foreach ($classMethods as $classMethod) {
            $methodName = $classMethod->name->name;
            $isBindingMethod = in_array($methodName, $bindingMethods, true);
            $isPublic = $classMethod->flags === Class_::MODIFIER_PUBLIC;
            if ($isBindingMethod && $isPublic) {
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
}
