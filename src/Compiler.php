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
     * @var CodeGen
     */
    private $codeGen;

    /**
     * @param string $classDir
     */
    public function __construct($classDir)
    {
        $this->classDir = $classDir;
        $this->codeGen = new CodeGen(
            new Parser(new Lexer),
            new BuilderFactory,
            new StandardPrettyPrinter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($class, array $args, Bind $bind)
    {
        $compiledClass = $this->compile($class, $bind);
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        $instance->bind = $bind;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($class, Bind $bind)
    {
        if (! $bind->bindings) {
            return $class;
        }
        $newClass = str_replace('\\', '_', $class) . '_' . ($bind) .'RayAop';
        if (class_exists($newClass)) {
            return $newClass;
        }
        $file = "{$this->classDir}/{$newClass}.php";
        if (file_exists($file)) {
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

        require $file;
    }
}
