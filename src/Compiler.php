<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;
use Ray\Aop\Exception\NotWritableException;

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
        if (! is_writable($classDir)) {
            throw new NotWritableException($classDir);
        }
        $this->classDir = $classDir;
        $this->codeGen = $codeGen ?: new CodeGen(
            (new ParserFactory)->newInstance(),
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
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }
        $newClass = $this->getNewClassName($class, $bind);
        if (class_exists($newClass)) {
            return $newClass;
        }
        $file = "{$this->classDir}/{$newClass}.php";
        if (file_exists($file)) {
            /** @noinspection PhpIncludeInspection */
            include $file;

            return $newClass;
        }
        $this->includeGeneratedCode($newClass, new ReflectionClass($class), $file, $bind);

        return $newClass;
    }

    /**
     * @param string        $class
     * @param BindInterface $bind
     *
     * @return bool
     */
    private function hasNoBinding($class, BindInterface $bind)
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    /**
     * @param string        $class
     * @param BindInterface $bind
     *
     * @return string
     */
    private function getNewClassName($class, BindInterface $bind)
    {
        $fileTime = filemtime((new \ReflectionClass($class))->getFileName());
        $newClass = sprintf("%s_%s", str_replace('\\', '_', $class), $bind->toString($fileTime));

        return $newClass;
    }

    /**
     * @param string        $class
     * @param BindInterface $bind
     *
     * @return bool
     */
    private function hasBoundMethod($class, BindInterface $bind)
    {
        $bindingMethods = array_keys($bind->getBindings());
        $hasMethod = false;
        foreach ($bindingMethods as $bindingMethod) {
            if (method_exists($class, $bindingMethod)) {
                $hasMethod = true;
            }
        }

        return $hasMethod;
    }

    /**
     * @param string          $newClass
     * @param ReflectionClass $sourceClass
     * @param string          $file
     */
    private function includeGeneratedCode($newClass, \ReflectionClass $sourceClass, $file, BindInterface $bind)
    {
        $code = $this->codeGen->generate($newClass, $sourceClass, $bind);
        file_put_contents($file, '<?php ' . PHP_EOL . $code);
        /** @noinspection PhpIncludeInspection */
        require $file;
    }
}
