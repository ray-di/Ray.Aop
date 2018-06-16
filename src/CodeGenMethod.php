<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Ray\Aop\Annotation\AbstractAssisted;

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

    private $reader;

    /**
     * @var AbstractAssisted
     */
    private $assisted;

    /**
     * @param \PhpParser\Parser                 $parser
     * @param \PhpParser\BuilderFactory         $factory
     * @param \PhpParser\PrettyPrinter\Standard $printer
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        Standard $printer
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
        $this->printer = $printer;
        $this->reader = new AnnotationReader;
    }

    /**
     * @param \ReflectionClass $class
     * @param BindInterface    $bind
     *
     * @return array
     */
    public function getMethods(\ReflectionClass $class, BindInterface $bind) : array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $stmts = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->assisted = $this->reader->getMethodAnnotation($method, AbstractAssisted::class);
            $isBindingMethod = in_array($method->name, $bindingMethods, true);
            /* @var $method \ReflectionMethod */
            if ($isBindingMethod && $method->isPublic()) {
                $stmts[] = $this->getMethod($method);
            }
        }

        return $stmts;
    }

    /**
     * Return method statement
     *
     * @param \ReflectionMethod $method
     *
     * @return \PhpParser\Node\Stmt\ClassMethod
     */
    private function getMethod(\ReflectionMethod $method)
    {
        $methodStmt = $this->factory->method($method->name);
        $params = $method->getParameters();
        foreach ($params as $param) {
            $methodStmt = $this->getMethodStatement($param, $methodStmt);
        }
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionType) {
            $this->setReturnType($returnType, $methodStmt);
        }
        $methodInsideStatements = $this->getMethodInsideStatement($method);
        $methodStmt->addStmts($methodInsideStatements);

        return $this->addMethodDocComment($methodStmt, $method);
    }

    /**
     * Return parameter reflection
     */
    private function getMethodStatement(\ReflectionParameter $param, Method $methodStmt) : Method
    {
        /* @var $paramStmt Param */
        $paramStmt = $this->factory->param($param->name);
        /* @var $param \ReflectionParameter */
        $this->setParameterType($param, $paramStmt);
        $this->setDefault($param, $paramStmt);
        $methodStmt->addParam($paramStmt);

        return $methodStmt;
    }

    private function addMethodDocComment(Method $methodStmt, \ReflectionMethod $method) : ClassMethod
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new Doc($docComment)]);
        }

        return $node;
    }

    /**
     * @return \PhpParser\Node[]
     */
    private function getMethodInsideStatement(\ReflectionMethod $method) : array
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new AopTemplateConverter($method));

        $code = file_get_contents(dirname(__DIR__) . '/template/AopTemplate.php');
        $node = $this->parser->parse($code)[0];
        /* @var $node \PhpParser\Node\Stmt\Class_ */
        $node = $node->getMethods()[0];
        // traverse
        $stmts = $traverser->traverse($node->stmts);

        return $stmts;
    }

    /**
     * @codeCoverageIgnore
     */
    private function setTypeHint(\ReflectionParameter $param, Param $paramStmt, \ReflectionClass $typeHint = null)
    {
        if ($typeHint) {
            $paramStmt->setTypeHint($typeHint->name);
        }
        if ($param->isArray()) {
            $paramStmt->setTypeHint('array');
        }
        if ($param->isCallable()) {
            $paramStmt->setTypeHint('callable');
        }
    }

    private function setDefault(\ReflectionParameter $param, Param $paramStmt)
    {
        if ($param->isDefaultValueAvailable()) {
            $paramStmt->setDefault($param->getDefaultValue());

            return;
        }
        if ($this->assisted && in_array($param->name, $this->assisted->values, true)) {
            $paramStmt->setDefault(null);
        }
    }

    private function setParameterType(\ReflectionParameter $param, Param $paramStmt)
    {
        $type = $param->getType();
        if (! $type) {
            return;
        }
        if ($type->allowsNull()) {
            $paramStmt->setTypeHint(new NullableType((string) $type));

            return;
        }
        $paramStmt->setTypeHint((string) $type);
    }

    private function setReturnType(\ReflectionType $returnType, Method $methodStmt)
    {
        $type = $returnType->allowsNull() ? new NullableType((string) $returnType) : (string) $returnType;
        $methodStmt->setReturnType($type);
    }
}
