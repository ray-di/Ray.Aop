<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\Parser;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Stmt\Class_;

final class CodeGen
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
     * @param \PHPParser\Parser                $parser
     * @param \PHPParser\BuilderFactory        $factory
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
     * @param string           $class
     * @param \ReflectionClass $sourceClass
     *
     * @return string
     */
    public function generate($class, \ReflectionClass $sourceClass)
    {
        $stmt = $this
            ->getClass($class, $sourceClass)
            ->addStmts($this->getMethods($sourceClass))
            ->getNode();
        $stmt = $this->addClassDocComment($stmt, $sourceClass);
        $code = $this->printer->prettyPrint([$stmt]);

        return $code;
    }

    /**
     * Return class statement
     *
     * @param string           $newClassName
     * @param \ReflectionClass $class
     *
     * @return Class_
     */
    private function getClass($newClassName, \ReflectionClass $class)
    {
        $parentClass = $class->name;
        $builder = $this->factory
            ->class($newClassName)
            ->extend($parentClass)
            ->implement('Ray\Aop\WeavedInterface')
            ->addStmt(
                $this->factory->property('isIntercepting')->makePrivate()->setDefault(true)
            )->addStmt(
                $this->factory->property('bind')->makePublic()
            );

        return $builder;
    }

    /**
     * Add class doc comment
     *
     * @param Class_           $node
     * @param \ReflectionClass $class
     *
     * @return \PHPParser\Node\Stmt\Class_
     */
    private function addClassDocComment(Class_ $node, \ReflectionClass $class)
    {
        $docComment = $class->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new \PHPParser\Comment\Doc($docComment)]);
        }

        return $node;
    }

    /**
     * Return method statements
     *
     * @param \ReflectionClass $class
     *
     * @return \PHPParser\Builder\Method[]
     */
    private function getMethods(\ReflectionClass $class)
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
     * @return \PHPParser\Node\Stmt\Class\Method_
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
    private function getMethodStatement(\ReflectionParameter $param, \PHPParser\Builder\Method $methodStmt)
    {
        /** @var $param \ReflectionParameter */
        $paramStmt = $this->factory->param($param->name);
        $typeHint = $param->getClass();
        if ($typeHint) {
            $paramStmt->setTypeHint($typeHint->name);
        }
        if ($param->isDefaultValueAvailable()) {
            $paramStmt->setDefault($param->getDefaultValue());
        }
        $methodStmt->addParam($paramStmt);

        return $methodStmt;
    }

    /**
     * @param \PHPParser\Builder\Method $methodStmt
     * @param \ReflectionMethod         $method
     *
     * @return \PhpParser\Node\Stmt\ClassMethod
     */
    private function addMethodDocComment(\PHPParser\Builder\Method $methodStmt, \ReflectionMethod $method)
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new \PHPParser\Comment\Doc($docComment)]);
        }
        return $node;
    }

    /**
     * @return \PHPParser\Node[]
     */
    private function getMethodInsideStatement()
    {
        $code = file_get_contents(dirname(__DIR__) . '/template/Weaved.php');
        $node = $this->parser->parse($code)[0];
        /** @var $node \PHPParser\Node\Stmt\Class_ */
        $node = $node->getMethods()[0];

        return $node->stmts;
    }
}
