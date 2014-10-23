<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

class CodeGen
{
    /**
     * @var \PHPParser_Parser
     */
    private $parser;

    /**
     * @var \PHPParser_BuilderFactory
     */
    private $factory;

    /**
     * @var \PHPParser_PrettyPrinter_Default
     */
    private $printer;

    /**
     * @param \PHPParser_Parser                $parser
     * @param \PHPParser_BuilderFactory        $factory
     * @param \PHPParser_PrettyPrinter_Default $printer
     */
    public function __construct(
        \PHPParser_Parser $parser,
        \PHPParser_BuilderFactory $factory,
        \PHPParser_PrettyPrinter_Default $printer
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
     * @return \PHPParser_Builder_Class
     */
    private function getClass($newClassName, \ReflectionClass $class)
    {
        $parentClass = $class->name;
        $builder = $this->factory
            ->class($newClassName)
            ->extend($parentClass)
            ->implement('Ray\Aop\WeavedInterface')
            ->addStmt(
                $this->factory->property('rayAopIntercept')->makePrivate()->setDefault(true)
            )->addStmt(
                $this->factory->property('rayAopBind')->makePublic()
            );

        return $builder;
    }

    /**
     * Add class doc comment
     *
     * @param \PHPParser_Node_Stmt_Class $node
     * @param \ReflectionClass           $class
     *
     * @return \PHPParser_Node_Stmt_Class
     */
    private function addClassDocComment(\PHPParser_Node_Stmt_Class $node, \ReflectionClass $class)
    {
        $docComment = $class->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new \PHPParser_Comment_Doc($docComment)]);
        }

        return $node;
    }

    /**
     * Return method statements
     *
     * @param \ReflectionClass $class
     *
     * @return \PHPParser_Builder_Method[]
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
     * @return \PHPParser_Node_Stmt_ClassMethod
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
     * @param \PHPParser_Builder_Method $methodStmt
     *
     * @return \PHPParser_Builder_Method
     */
    private function getMethodStatement(\ReflectionParameter $param, \PHPParser_Builder_Method $methodStmt)
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
     * Add method doc comment
     *
     * @param \PHPParser_Builder_Method $methodStmt
     * @param \ReflectionMethod         $method
     *
     * @return \PHPParser_Node_Stmt_ClassMethod
     */
    private function addMethodDocComment(\PHPParser_Builder_Method $methodStmt, \ReflectionMethod $method)
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new \PHPParser_Comment_Doc($docComment)]);
        }
        return $node;
    }

    /**
     * @return \PHPParser_Node[]
     */
    private function getMethodInsideStatement()
    {
        $code = file_get_contents(__DIR__ . '/Compiler/Template.php');
        $node = $this->parser->parse($code)[0];
        /** @var $node \PHPParser_Node_Stmt_Class */
        $node = $node->getMethods()[0];

        return $node->stmts;
    }
}
