<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader as DoctrineReader;
use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Parser;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionClass;

use function array_merge;
use function assert;
use function implode;
use function serialize;

final class CodeGen implements CodeGenInterface
{
    /** @var Parser */
    private $parser;

    /** @var BuilderFactory */
    private $factory;

    /** @var CodeGenMethod */
    private $codeGenMethod;

    /** @var DoctrineReader */
    private $reader;

    /** @var AopClassName */
    private $aopClassName;

    /** @var VisitorFactory */
    private $visitoryFactory;

    /**
     * @throws AnnotationException
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
        $this->codeGenMethod = new CodeGenMethod($parser);
        $this->reader = ServiceLocator::getReader();
        $this->aopClassName = $aopClassName;
        $this->visitoryFactory = new VisitorFactory($this);
    }

    /**
     * {@inheritdoc}
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind): Code
    {
        $visitor = ($this->visitoryFactory)($sourceClass);
        assert($visitor->class instanceof Class_);
        $methods = $this->codeGenMethod->getMethods($bind, $visitor);
        $propStms = $this->getAopProps($sourceClass);
        $classStm = $visitor->class;
        $newClassName = ($this->aopClassName)((string) $visitor->class->name, $bind->toString(''));
        $classStm->name = new Identifier($newClassName);
        $classStm->extends = new Name('\\' . $sourceClass->name);
        $classStm->implements[] = new Name('WeavedInterface');
        /** @var array<int, Stmt> $stmts */
        $stmts = array_merge($propStms, $methods);
        $classStm->stmts = $stmts;
        $ns = $this->getNamespace($visitor);
        $stmt = $this->factory->namespace($ns)
            ->addStmt($this->factory->use(WeavedInterface::class))
            ->addStmt($this->factory->use(ReflectiveMethodInvocation::class)->as('Invocation'))
            ->addStmts($visitor->use)
            ->addStmt($classStm)
            ->getNode();

        return new Code(array_merge($visitor->declare, [$stmt]));
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getClassAnnotation(ReflectionClass $class): string
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);

        return serialize($classAnnotations);
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return Property[]
     */
    private function getAopProps(ReflectionClass $class): array
    {
        $pros = [];
        $pros[] = $this->factory
            ->property('bind')
            ->makePublic()
            ->getNode();

        $pros[] =
            $this->factory->property('bindings')
                ->makePublic()
                ->setDefault([])
                ->getNode();

        $pros[] = $this->factory
            ->property('methodAnnotations')
            ->setDefault($this->getMethodAnnotations($class))
            ->makePublic()
            ->getNode();
        $pros[] = $this->factory
            ->property('classAnnotations')
            ->setDefault($this->getClassAnnotation($class))
            ->makePublic()
            ->getNode();
        $pros[] = $this->factory
            ->property('isAspect')
            ->makePrivate()
            ->setDefault(true)
            ->getNode();

        return $pros;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getMethodAnnotations(ReflectionClass $class): string
    {
        $methodsAnnotation = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if ($annotations === []) {
                continue;
            }

            $methodsAnnotation[$method->name] = $annotations;
        }

        return serialize($methodsAnnotation);
    }

    /**
     * @return string|null
     */
    private function getNamespace(CodeVisitor $source)
    {
        $parts = $source->namespace->name->parts ?? [];
        $ns = implode('\\', $parts);

        return $ns ? $ns : null;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }
}
