<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;
use Ray\Aop\Exception\NotWritableException;
use Ray\Aop\Php71\BindInterface;
use Ray\Aop\Php71\CodeGenInterface;
use Ray\Aop\Php71\CompilerInterface;

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
    public function __construct(string $classDir, CodeGenInterface $codeGen = null)
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
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        $compiledClass = $this->compile($class, $bind);
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        $instance->bindings = $bind->getBindings();

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $class, BindInterface $bind) : string
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
            /** @noinspection UntrustedInclusionInspection */
            /** @noinspection PhpIncludeInspection */
            include $file;

            return $newClass;
        }
        $this->includeGeneratedCode($newClass, new ReflectionClass($class), $file, $bind);

        return $newClass;
    }

    private function hasNoBinding($class, BindInterface $bind) : bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    private function getNewClassName($class, BindInterface $bind) : string
    {
        $newClass = sprintf('%s_%s', str_replace('\\', '_', $class), $bind->toString(''));

        return $newClass;
    }

    private function hasBoundMethod(string $class, BindInterface $bind) : bool
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

    private function includeGeneratedCode($newClass, \ReflectionClass $sourceClass, string $file, BindInterface $bind)
    {
        $code = $this->codeGen->generate($newClass, $sourceClass, $bind);
        file_put_contents($file, '<?php ' . PHP_EOL . $code);
        /** @noinspection PhpIncludeInspection */
        require $file;
    }
}
