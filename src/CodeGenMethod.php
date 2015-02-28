<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PHPParser\Builder\Method;

final class CodeGenMethod
{
    /**
     * @var \PHPParser\Parser
     */
    private $parser;

    /**
     * @var \PHPParser\BuilderFactory
     */
    private $factory;

    /**
     * @var \PHPParser\PrettyPrinter\Standard
     */
    private $printer;

    /**
     * @param \PHPParser\Parser                 $parser
     * @param \PHPParser\BuilderFactory         $factory
     * @param \PHPParser\PrettyPrinter\Standard $printer
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

    /**
     * @param \ReflectionClass $class
     *
     * @return array
     */
    public function getMethods(\ReflectionClass $class)
    {
        $stmts = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            /** @var $method \ReflectionMethod */
            if ($method->isPublic()) {
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
        $methodInsideStatements = $this->getMethodInsideStatement();
        $methodStmt->addStmts($methodInsideStatements);
        $node = $this->addMethodDocComment($methodStmt, $method);

        return $node;
    }

    /**
     * Return parameter reflection
     *
     * @param \ReflectionParameter      $param
     * @param \PHPParser\Builder\Method $methodStmt
     *
     * @return \PHPParser\Builder\Method
     */
    private function getMethodStatement(\ReflectionParameter $param, Method $methodStmt)
    {
        /** @var $paramStmt Param */
        $paramStmt = $this->factory->param($param->name);
        /** @var $param \ReflectionParameter */
        $typeHint = $param->getClass();
        $this->setTypeHint($param ,$paramStmt ,$typeHint);
        $this->setDefault($param, $paramStmt);
        $methodStmt->addParam($paramStmt);

        return $methodStmt;
    }

    /**
     * @param Method            $methodStmt
     * @param \ReflectionMethod $method
     *
     * @return \PhpParser\Node\Stmt\ClassMethod
     */
    private function addMethodDocComment(Method $methodStmt, \ReflectionMethod $method)
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new Doc($docComment)]);
        }

        return $node;
    }

    /**
     * @return \PHPParser\Node[]
     */
    private function getMethodInsideStatement()
    {
        $code = file_get_contents(dirname(__DIR__) . '/src-data/CodeGenTemplate.php');
        $node = $this->parser->parse($code)[0];
        /** @var $node \PHPParser\Node\Stmt\Class_ */
        $node = $node->getMethods()[0];

        return $node->stmts;
    }

    /**
     * @param \ReflectionParameter $param
     * @param Param                $paramStmt
     * @param \ReflectionClass     $typeHint
     */
    private function setTypeHint(\ReflectionParameter $param ,Param $paramStmt ,\ReflectionClass $typeHint = null)
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

    /**
     * @param \ReflectionParameter $param
     * @param Param                $paramStmt
     */
    private function setDefault(\ReflectionParameter $param, $paramStmt)
    {
        if ($param->isDefaultValueAvailable()) {
            $paramStmt->setDefault($param->getDefaultValue());
        }
    }
}
