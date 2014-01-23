<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PHPParser_BuilderFactory;
use PHPParser_Parser;
use PHPParser_PrettyPrinterAbstract;
use ReflectionClass;
use ReflectionMethod;
use PHPParser_Comment_Doc;
use PHPParser_Builder_Class;
use PHPParser_Node_Stmt_Class;
use PHPParser_Builder_Method;
use PHPParser_Lexer;
use Serializable;

/**
 * AOP compiler
 */
final class Compiler implements CompilerInterface, Serializable
{
    /**
     * @var string
     */
    public $classDir;

    /**
     * @var \PHPParser_Parser
     */
    private $parser;

    /**
     * @var \PHPParser_BuilderFactory
     */
    private $factory;

    /**
     * @param string                          $classDir
     * @param PHPParser_PrettyPrinterAbstract $printer
     */
    public function __construct(
        $classDir,
        PHPParser_PrettyPrinterAbstract $printer
    ) {
        $this->classDir = $classDir;
        $this->printer = $printer;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, Bind $bind)
    {
        $this->parser = new PHPParser_Parser(new PHPParser_Lexer);
        $this->factory = new PHPParser_BuilderFactory;

        $refClass = new ReflectionClass($class);
        $newClassName = $this->getClassName($refClass, $bind);
        if (class_exists($newClassName, false)) {
            return $newClassName;
        }
        $file = $this->classDir . "/{$newClassName}.php";
        $stmt = $this
                ->getClass($newClassName, $refClass)
                ->addStmts($this->getMethods($refClass, $bind))
                ->getNode();
        $stmt = $this->addClassDocComment($stmt, $refClass);
        $code = $this->printer->prettyPrint([$stmt]);
        file_put_contents($file, '<?php ' . PHP_EOL . $code);
        include_once $file;

        return $newClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($class, array $args, Bind $bind)
    {
        $class = $this->compile($class, $bind);
        $instance = (new ReflectionClass($class))->newInstanceArgs($args);
        $instance->rayAopBind = $bind;

        return $instance;
    }

    /**
     * Return new class name
     *
     * @param \ReflectionClass $class
     * @param Bind             $bind
     *
     * @return string
     */
    private function getClassName(\ReflectionClass $class, Bind $bind)
    {
        $className = str_replace('\\', '_', $class->getName()) . '_' . md5($bind) .'RayAop';

        return $className;
    }

    /**
     * Return class statement
     *
     * @param string          $newClassName
     * @param ReflectionClass $class
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
     * @param PHPParser_Node_Stmt_Class $node
     * @param ReflectionClass           $class
     *
     * @return PHPParser_Node_Stmt_Class
     */
    private function addClassDocComment(PHPParser_Node_Stmt_Class $node, \ReflectionClass $class)
    {
        $docComment = $class->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new PHPParser_Comment_Doc($docComment)]);
        }

        return $node;
    }

    /**
     * Return method statements
     *
     * @param ReflectionClass $class
     *
     * @return \PHPParser_Builder_Method[]
     */
    private function getMethods(ReflectionClass $class)
    {
        $stmts = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            /** @var $method ReflectionMethod */
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
     * @return \PHPParser_Builder_Method
     */
    private function getMethod(\ReflectionMethod $method)
    {
        $methodStmt = $this->factory->method($method->name);
        $params = $method->getParameters();
        foreach ($params as $param) {
            /** @var $param \ReflectionParameter */
            $paramStmt = $this->factory->param($param->name);
            $typeHint = $param->getClass();
            if ($typeHint) {
                $paramStmt->setTypeHint($typeHint->name);
            }
            if ($param->isDefaultValueAvailable()) {
                $paramStmt->setDefault($param->getDefaultValue());
            }
            $methodStmt->addParam(
                $paramStmt
            );
        }
        $methodInsideStatements = $this->getMethodInsideStatement();
        $methodStmt->addStmts($methodInsideStatements);
        $node = $this->addMethodDocComment($methodStmt, $method);

        return $node;
    }

    /**
     * Add method doc comment
     *
     * @param PHPParser_Builder_Method $methodStmt
     * @param ReflectionMethod         $method
     *
     * @return \PHPParser_Node_Stmt_ClassMethod
     */
    private function addMethodDocComment(PHPParser_Builder_Method $methodStmt, \ReflectionMethod $method)
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new PHPParser_Comment_Doc($docComment)]);
        }
        return $node;
    }

    /**
     * @return \PHPParser_Node[]
     */
    private function getMethodInsideStatement()
    {
        $code = $this->getWeavedMethodTemplate();
        $node = $this->parser->parse($code)[0];
        /** @var $node \PHPParser_Node_Stmt_Class */
        $node = $node->getMethods()[0];

        return $node->stmts;
    }

    /**
     * @return string
     */
    private function getWeavedMethodTemplate()
    {

        return file_get_contents(__DIR__ . '/Compiler/Template.php');
    }

    public function serialize()
    {
        unset($this->factory);
        unset($this->parser);
        return serialize([$this->classDir, $this->printer]);
    }

    public function unserialize($data)
    {
        list($this->classDir, $this->printer) = unserialize($data);
    }
}
