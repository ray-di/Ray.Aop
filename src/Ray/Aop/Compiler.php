<?php

/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PHPParser_BuilderFactory;
use PHPParser_Lexer;
use PHPParser_Parser;
use PHPParser_PrettyPrinter_Zend;
use PHPParser_PrettyPrinterAbstract;
use ReflectionClass;
use ReflectionMethod;
use TokenReflection\ReflectionParameter;

/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
final class Compiler implements CompilerInterface
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
     * @var string
     */
    private $classDir;

    /**
     * @param PHPParser_Parser                $parser
     * @param PHPParser_BuilderFactory        $factory
     * @param PHPParser_PrettyPrinterAbstract $printer
     * @param string                          $classDir
     */
    public function __construct(
        $classDir = null,
        PHPParser_Parser $parser = null,
        PHPParser_BuilderFactory $factory = null,
        PHPParser_PrettyPrinterAbstract $printer = null
    ) {
        ini_set('xdebug.max_nesting_level', 2000);
        $this->classDir = $classDir ? : sys_get_temp_dir();
        $this->parser = $parser ? : new PHPParser_Parser(new PHPParser_Lexer);
        $this->factory = $factory ? : new PHPParser_BuilderFactory;
        $this->printer = $printer ? : new PHPParser_PrettyPrinter_Zend;
    }

    /**
     * Return new aspect weaved object instance
     *
     * @param       $class
     * @param array $args
     * @param Bind  $bind
     *
     * @return object
     */
    public function newInstance($class, array $args = [], Bind $bind)
    {
        $class = $this->compile($class, $bind);
        $instance = (new ReflectionClass($class))->newInstanceArgs($args);
        $instance->rayAopBind = $bind;

        return $instance;
    }

    /**
     * @param      $class
     * @param Bind $bind
     *
     * @return string
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
     * @param ReflectionClass $class
     * @param Bind            $bind
     *
     * @return array
     */
    private function getMethods(ReflectionClass $class, Bind $bind)
    {
        $stmts = [];
        $methods = $class->getMethods();
        $weavedMethod = array_keys((array)$bind);
        foreach ($methods as $method) {
            if (in_array($method->name, $weavedMethod)) {
                $stmts[] = $this->getMethod($method);
            }
        }

        return $stmts;
    }

    /**
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
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
        $node = $parser->parse($code)[0];
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
