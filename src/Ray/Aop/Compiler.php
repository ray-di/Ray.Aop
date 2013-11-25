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

/**
 * Aspect weave compiler
 */
class Compiler implements CompilerInterface
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
     * @param PHPParser_Parser                $parser
     * @param PHPParser_BuilderFactory        $factory
     * @param PHPParser_PrettyPrinterAbstract $printer
     */
    public function __construct(
        $classDir,
        PHPParser_PrettyPrinterAbstract $printer,
        PHPParser_Parser $parser,
        PHPParser_BuilderFactory $factory
    ) {
        $this->classDir = $classDir;
        $this->printer = $printer;
        $this->parser = $parser;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, Bind $bind)
    {
        $class = new ReflectionClass($class);
        $newClassName = $this->getClassName($class, $bind);
        if (class_exists($newClassName, false)) {
            return $newClassName;
        }
        $file = $this->classDir . "/{$newClassName}.php";
        $stmts = [
            $this->getClass($newClassName, $class)
                ->addStmts($this->getMethods($class, $bind))
                ->getNode()
        ];
        $code = $this->printer->prettyPrint($stmts);
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
        $className = str_replace('\\', '', $class->getName()) . 'Ray' . spl_object_hash($bind) . 'Aop';

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

        return $methodStmt;
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

        /** @var $node \PHPParser_Node_Stmt_ClassMethod */

        return $node->stmts;
    }

    /**
     * @return string
     */
    private function getWeavedMethodTemplate()
    {

        return file_get_contents(__DIR__ . '/Compiler/Template.php');
    }
}
