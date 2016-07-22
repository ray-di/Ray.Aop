<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
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
    private $assisted = [];

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
    public function getMethods(\ReflectionClass $class, BindInterface $bind)
    {
        $bindingMethods = array_keys($bind->getBindings());
        $stmts = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->assisted = $this->reader->getMethodAnnotation($method, AbstractAssisted::class);
            $isBindingMethod = in_array($method->getName(), $bindingMethods, true);
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
        $isOverPhp7 = version_compare(PHP_VERSION, '7.0.0') >= 0;
        foreach ($params as $param) {
            $methodStmt = $this->getMethodStatement($param, $methodStmt, $isOverPhp7);
        }
        if ($isOverPhp7) {
            $returnType = (string) $method->getReturnType();
            $this->setReturnType($returnType, $methodStmt);
        }
        $methodInsideStatements = $this->getMethodInsideStatement();
        $methodStmt->addStmts($methodInsideStatements);
        return $this->addMethodDocComment($methodStmt, $method);
    }

    /**
     * Return parameter reflection
     *
     * @param \ReflectionParameter      $param
     * @param \PhpParser\Builder\Method $methodStmt
     * @param bool                      $isOverPhp7
     *
     * @return \PhpParser\Builder\Method
     */
    private function getMethodStatement(\ReflectionParameter $param, Method $methodStmt, $isOverPhp7)
    {
        /** @var $paramStmt Param */
        $paramStmt = $this->factory->param($param->name);
        /* @var $param \ReflectionParameter */
        $typeHint = $param->getClass();
        $this->setParameterType($param, $paramStmt, $isOverPhp7, $typeHint);
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
     * @return \PhpParser\Node[]
     */
    private function getMethodInsideStatement()
    {
        $code = file_get_contents(dirname(__DIR__) . '/src-data/CodeGenTemplate.php');
        $node = $this->parser->parse($code)[0];
        /** @var $node \PhpParser\Node\Stmt\Class_ */
        $node = $node->getMethods()[0];

        return $node->stmts;
    }

    /**
     * @param \ReflectionParameter $param
     * @param Param                $paramStmt
     * @param \ReflectionClass     $typeHint
     *
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

    /**
     * @param \ReflectionParameter $param
     * @param Param                $paramStmt
     */
    private function setDefault(\ReflectionParameter $param, $paramStmt)
    {
        if ($param->isDefaultValueAvailable()) {
            $paramStmt->setDefault($param->getDefaultValue());

            return;
        }
        if ($this->assisted && in_array($param->getName(), $this->assisted->values, true)) {
            $paramStmt->setDefault(null);
        }
    }

    /**
     * @param \ReflectionParameter $param
     * @param Param                $paramStmt
     * @param bool                 $isOverPhp7
     * @param \ReflectionClass     $typeHint
     */
    private function setParameterType(\ReflectionParameter $param, Param $paramStmt, $isOverPhp7, \ReflectionClass $typeHint = null)
    {
        if (! $isOverPhp7) {
            $this->setTypeHint($param, $paramStmt, $typeHint); // @codeCoverageIgnore

            return; // @codeCoverageIgnore
        }
        $type = $param->getType();
        if ($type) {
            $paramStmt->setTypeHint((string) $type);
        }
    }

    /**
     * @param string $returnType
     * @param Method $methodStmt
     */
    private function setReturnType($returnType, Method $methodStmt)
    {
        if ($returnType && method_exists($methodStmt, 'setReturnType')) {
            $methodStmt->setReturnType($returnType); // @codeCoverageIgnore
        }
    }
}
