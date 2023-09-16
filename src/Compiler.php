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
use function file_put_contents;
use function is_writable;
use function method_exists;
use function sprintf;
use function str_replace;

final class Compiler implements CompilerInterface
{
    /** @var string */
    public $classDir;

    /** @var CodeGen */
    private $codeGen;

    /** @throws AnnotationException */
    public function __construct(string $classDir)
    {
        if (! is_writable($classDir)) {
            throw new NotWritableException($classDir);
        }

        $this->classDir = $classDir;
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
     * {@inheritDoc}
     */
    public function compile(string $class, BindInterface $bind): string
    {
        if ($this->hasNoBinding($class, $bind)) {
            return $class;
        }

        $className = new AopPostfixClassName($class, (string) $bind);
        if (class_exists($className->fqn, false)) {
            return $className->fqn;
        }

        $this->requireFile($className, new \ReflectionClass($class), $bind);

        return $className->fqn;
    }

    /** @param class-string $class */
    private function hasNoBinding(string $class, BindInterface $bind): bool
    {
        $hasMethod = $this->hasBoundMethod($class, $bind);

        return ! $bind->getBindings() && ! $hasMethod;
    }

    /** @param class-string $class */
    private function hasBoundMethod(string $class, BindInterface $bind): bool
    {
        $bindingMethods = array_keys($bind->getBindings());
        $hasMethod = false;
        foreach ($bindingMethods as $bindingMethod) {
            if (! method_exists($class, $bindingMethod)) {
                continue;
            }

            $hasMethod = true;
        }

        return $hasMethod;
    }

    /** @param \ReflectionClass<object> $sourceClass */
    private function requireFile(AopPostfixClassName $className, \ReflectionClass $sourceClass, BindInterface $bind): void
    {
        $file = $this->getFileName($className->fqn);
        if (! file_exists($file)) {
            $aopCode = (new AopCodeGen())->generate($sourceClass, $className->postFix, $bind);
            file_put_contents($file, $aopCode);
        }

        require_once $file;
        class_exists($className->fqn); // ensure class is created
    }

    private function getFileName(string $aopClassName): string
    {
        $flatName = str_replace('\\', '_', $aopClassName);

        return sprintf('%s/%s.php', $this->classDir, $flatName);
    }

    private function createCodeGen(): CodeGen
    {
        $parser = (new ParserFactory())->newInstance();
        $factory = new BuilderFactory();

        return new CodeGen(
            $factory,
            new VisitorFactory($parser),
            new AopClass($parser, $factory, $this->aopClassName)
        );
    }
}
