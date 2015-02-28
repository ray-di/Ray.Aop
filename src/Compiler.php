<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;
use ReflectionClass;

final class Compiler implements CompilerInterface
{
    /**
     * @var string
     */
    public $classDir;

    /**
     * @var CodeGenInterface
     */
    private $codeGen;

    /**
     * @param string           $classDir
     * @param CodeGenInterface $codeGen
     */
    public function __construct($classDir, CodeGenInterface $codeGen = null)
    {
        $this->classDir = $classDir;
        $this->codeGen = $codeGen ?: new CodeGen(
            new Parser(new Lexer()),
            new BuilderFactory(),
            new StandardPrettyPrinter()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($class, array $args, BindInterface $bind)
    {
        $compiledClass = $this->compile($class, $bind);
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        $instance->bindings = $bind->getBindings();

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, BindInterface $bind)
    {
        if (! $bind->getBindings()) {
            return $class;
        }
        $fileTime = filemtime((new \ReflectionClass($class))->getFileName());
        $newClass = sprintf("%s_%s", str_replace('\\', '_', $class), $bind->toString($fileTime));
        if (class_exists($newClass)) {
            return $newClass;
        }
        $file = "{$this->classDir}/{$newClass}.php";
        if (file_exists($file)) {
            /** @noinspection PhpIncludeInspection */
            include $file;

            return $newClass;
        }
        $this->includeGeneratedCode($newClass, new ReflectionClass($class), $file);

        return $newClass;
    }

    /**
     * @param string          $newClass
     * @param ReflectionClass $sourceClass
     * @param string          $file
     */
    private function includeGeneratedCode($newClass, \ReflectionClass $sourceClass, $file)
    {
        $code = $this->codeGen->generate($newClass, $sourceClass);
        file_put_contents($file, '<?php ' . PHP_EOL . $code);
        /** @noinspection PhpIncludeInspection */
        require $file;
    }
}
