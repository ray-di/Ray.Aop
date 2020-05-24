<?php

declare(strict_types=1);

namespace Ray\Aop;

final class Weaver
{
    /**
     * @var BindInterface
     */
    private $bind;

    /**
     * @var string
     */
    private $bindName;

    /**
     * @var string
     */
    private $classDir;

    /**
     * @var AopClassName
     */
    private $aopClassName;

    /**
     * @var Compiler
     */
    private $compiler;

    public function __construct(BindInterface $bind, string $classDir)
    {
        $this->bind = $bind;
        $this->bindName = $bind->toString('');
        $this->compiler = new Compiler($classDir);
        $this->classDir = $classDir;
        $this->aopClassName = new AopClassName($classDir);
    }

    /**
     * @param class-string      $class
     * @param array<int, mixed> $args
     */
    public function newInstance(string $class, array $args) : object
    {
        $aopClass = $this->weave($class);
        $instance = (new ReflectionClass($aopClass))->newInstanceArgs($args);
        assert(isset($instance->bindings));
        $instance->bindings = $this->bind->getBindings();

        return $instance;
    }

    /**
     * @param class-string $class
     *
     * @return class-string
     */
    public function weave(string $class) : string
    {
        $aopClass = ($this->aopClassName)($class, $this->bindName);
        if (class_exists($aopClass, false)) {
            return $aopClass;
        }
        if ($this->loadClass($aopClass)) {
            assert(class_exists($aopClass));

            return $aopClass;
        }
        $this->compiler->compile($class, $this->bind);
        assert(class_exists($aopClass));

        return $aopClass;
    }

    private function loadClass(string $class) : bool
    {
        $file = sprintf('%s/%s.php', $this->classDir, str_replace('\\', '_', $class));
        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }
}
