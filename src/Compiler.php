<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(string $classDir)
    {
        if (! is_writable($classDir)) {
            throw new NotWritableException($classDir);
        }
        $this->classDir = $classDir;
        $this->codeGen = new CodeGen(
            (new ParserFactory)->newInstance(),
            new BuilderFactory,
            new Standard(['shortArraySyntax' => true])
        );
    }

    public function __sleep()
    {
        return ['classDir'];
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __wakeup()
    {
        $this->__construct($this->classDir);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
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
     *
     * @throws \ReflectionException
     */
    public function compile($class, BindInterface $bind) : string
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
        return sprintf('%s_%s', str_replace('\\', '_', $class), $bind->toString(''));
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
        file_put_contents($file, $code . PHP_EOL);
        /** @noinspection PhpIncludeInspection */
        require $file;
    }
}
