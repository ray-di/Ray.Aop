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
use ReflectionParameter;
use PHPParser_PrettyPrinter_Default;

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
     * @var CodeGen
     */
    private $codeGen;

    /**
     * @param string                          $classDir
     * @param PHPParser_PrettyPrinterAbstract $printer
     */
    public function __construct($classDir)
    {
        $this->classDir = $classDir;
        $this->codeGen = new CodeGen(
            new PHPParser_Parser(new PHPParser_Lexer),
            new PHPParser_BuilderFactory,
            new PHPParser_PrettyPrinter_Default
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAopClassDir()
    {
        return $this->classDir;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, Bind $bind)
    {
        $newClassName = $this->getClassName($class, $bind);
        if (class_exists($newClassName)) {
            return $newClassName;
        }
        $code = $this->codeGen->generate($newClassName, new ReflectionClass($class), $this->classDir);
        $file = $this->classDir . "/{$newClassName}.php";
        file_put_contents($file, '<?php ' . PHP_EOL . $code);
        include_once $file;

        return $newClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($class, array $args, Bind $bind)
    {
        $instance = $this->noBindNewInstance($class, $args, $bind);
        $instance->rayAopBind = $bind;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function noBindNewInstance($class, array $args, Bind $bind)
    {
        $class = $this->compile($class, $bind);
        $instance = (new ReflectionClass($class))->newInstanceArgs($args);

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
    private function getClassName($class, Bind $bind)
    {
        $className = str_replace('\\', '_', $class) . '_' . md5($bind) .'RayAop';

        return $className;
    }

    public function serialize()
    {
        return serialize([$this->classDir]);
    }

    public function unserialize($data)
    {
        list($this->classDir) = unserialize($data);
    }
}
