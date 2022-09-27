<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationException;
use PhpParser\BuilderFactory;
use Ray\Aop\Exception\NotWritableException;

use function array_keys;
use function assert;
use function class_exists;
use function file_exists;
use function is_writable;
use function method_exists;
use function sprintf;
use function str_replace;

final class Compiler implements CompilerInterface
{
    /** @var string */
    public $classDir;

    /** @var CodeGenInterface */
    private $codeGen;

    /** @var AopClassName */
    private $aopClassName;

    /**
     * @throws AnnotationException
     */
    public function __construct(string $classDir)
    {
        if (! is_writable($classDir)) {
            throw new NotWritableException($classDir);
        }

        $this->classDir = $classDir;
        $this->aopClassName = new AopClassName($classDir);
        $parser = (new ParserFactory())->newInstance();
        $factory = new BuilderFactory();
        $aopClassName = new AopClassName($classDir);
        $this->codeGen = new CodeGen(
            $factory,
            new VisitorFactory($parser),
            new AopClass($parser, $factory, $aopClassName)
        );
    }

    /**
     * @return list<string>
     */
    public function __sleep()
    {
        return ['classDir'];
    }

    /**
     * @throws AnnotationException
     */
    public function __wakeup()
    {
        $this->__construct($this->classDir);
    }

    /**
     * {@inheritDoc}
     *
     * @template T of object
     */
    public function newInstance(string $class, array $args, BindInterface $bind)
    {
        $compiledClass = $this->compile($class, $bind);
        assert(class_exists($compiledClass));
        $instance = (new ReflectionClass($compiledClass))->newInstanceArgs($args);
        if (isset($instance->bindings)) {
            $instance->bindings = $bind->getBindings();
        }

        assert($instance instanceof $class);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $class, BindInterface $bind): string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }

        $aopClass = ($this->aopClassName)($class, (string) $bind);
        if (class_exists($aopClass, false)) {
            return $aopClass;
        }

        /** @var class-string $aopClass */
        $this->requireFile($aopClass, new ReflectionClass($class), $bind);

        return $aopClass;
    }

    /**
     * @param class-string $class
     */
    private function hasNoBinding(string $class, BindInterface $bind): bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    /**
     * @param class-string $class
     */
    private function hasBoundMethod(string $class, BindInterface $bind): bool
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
     * @param class-string             $aopClassName
     * @param \ReflectionClass<object> $sourceClass
     */
    private function requireFile(string $aopClassName, \ReflectionClass $sourceClass, BindInterface $bind): void
    {
        $file = $this->getFileName($aopClassName);
        if (! file_exists($file)) {
            $code = $this->codeGen->generate($sourceClass, $bind);
            $code->save($file);
            assert(file_exists($file));
        }

        require_once $file;
        class_exists($aopClassName); // ensue class is created
    }

    private function getFileName(string $aopClassName): string
    {
        $flatName = str_replace('\\', '_', $aopClassName);

        return sprintf('%s/%s.php', $this->classDir, $flatName);
    }
}
