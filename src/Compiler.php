<?php

declare(strict_types=1);

namespace Ray\Aop;

use function class_exists;
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
     * @var AopClassName
     */
    private $aopClassName;

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
        $this->aopClassName = new AopClassName;
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
        assert(class_exists($compiledClass));
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        assert(isset($instance->bindings));
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
        $aopClassName = ($this->aopClassName)($class, $bind->toString(''));
        if (class_exists($aopClassName, false)) {
            return $aopClassName;
        }
        assert(class_exists($class));
        $this->requireFile($aopClassName, new ReflectionClass($class), $bind);

        return $aopClassName;
    }

    private function hasNoBinding(string $class, BindInterface $bind) : bool
    {
        assert(class_exists($class));
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    private function hasBoundMethod(string $class, BindInterface $bind) : bool
    {
        assert(class_exists($class));
        $bindingMethods = array_keys($bind->getBindings());
        $hasMethod = false;
        foreach ($bindingMethods as $bindingMethod) {
            if (method_exists($class, $bindingMethod)) {
                $hasMethod = true;
            }
        }

        return $hasMethod;
    }

    private function requireFile(string $aopClassName, \ReflectionClass $sourceClass, BindInterface $bind) : void
    {
        $code = $this->codeGen->generate($sourceClass, $bind);
        $file = $code->save($this->classDir, $aopClassName);
        assert(file_exists($file));
        require_once $file;
        class_exists($aopClassName); // ensue class is created
    }
}
