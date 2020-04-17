<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

final class CodeGenMethod
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var \PhpParser\BuilderFactory
     */
    private $factory;

    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printer;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        Standard $printer
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
        $this->printer = $printer;
    }

    public function getMethods(\ReflectionClass $class, BindInterface $bind, CodeVisitor $code) : array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $classMethods = $code->classMethod;
        $methods = [];
        foreach ($classMethods as $classMethod) {
            $methodName = $classMethod->name->name;
            $method = new \ReflectionMethod($class->name, $methodName);
            $isBindingMethod = in_array($methodName, $bindingMethods, true);
            assert($method instanceof \ReflectionMethod);
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

    private function getTemplateMethodNodeStmts(?NodeAbstract $returnType) : array
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

    private function isReturnVoid(?NodeAbstract $returnType) : bool
    {
        return $returnType instanceof Identifier && $returnType->name === 'void';
    }
}
