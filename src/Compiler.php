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
    public function newInstance(string $class, array $args, BindInterface $bind)
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
    public function compile(string $class, BindInterface $bind) : string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }
        $baseName = $this->getBaseName($class, $bind);
        $newClassName = 'RayAop\\' . $baseName;
        if (class_exists($newClassName)) {
            return $newClassName;
        }
        $file = "{$this->classDir}/{$baseName}.php";
        if (file_exists($file)) {
            /** @noinspection UntrustedInclusionInspection */
            /** @noinspection PhpIncludeInspection */
            include $file;

            return $newClassName;
        }
        $this->includeGeneratedCode($baseName, new ReflectionClass($class), $file, $bind);

        return $newClassName;
    }

    private function hasNoBinding($class, BindInterface $bind) : bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    private function getBaseName($class, BindInterface $bind) : string
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
