<?php

declare(strict_types=1);

namespace Ray\Aop;

use function array_merge;
use Doctrine\Common\Annotations\AnnotationReader;
use function implode;
use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Ray\Aop\Exception\InvalidSourceClassException;

final class CodeGen implements CodeGenInterface
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var \PhpParser\BuilderFactory
     */
    private $factory;

    /**
     * @var CodeGenMethod
     */
    private $codeGenMethod;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var AopClassName
     */
    private $aopClassName;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
        $this->codeGenMethod = new CodeGenMethod($parser, $factory);
        $this->reader = new AnnotationReader;
        $this->aopClassName = $aopClassName;
    }

    /**
     * {@inheritdoc}
     *
     * @param \ReflectionClass<object> $sourceClass
     */
    public function generate(\ReflectionClass $sourceClass, BindInterface $bind) : Code
    {
        $source = $this->getVisitorCode($sourceClass);
        assert($source->class instanceof Class_);
        $methods = $this->codeGenMethod->getMethods($sourceClass, $bind, $source);
        $propStms = $this->getAopProps($sourceClass);
        $classStm = $source->class;
        $newClassName = ($this->aopClassName)((string) $source->class->name, $bind->toString(''));
        $classStm->name = new Identifier($newClassName);
        $classStm->extends = new Name('\\' . $sourceClass->name);
        $classStm->implements[] = new Name('WeavedInterface');
        /** @var array<int, Stmt> $stmts */
        $stmts = array_merge($propStms, $methods);
        $classStm->stmts = $stmts;
        $ns = $this->getNamespace($source);
        $stmt = $this->factory->namespace($ns)
            ->addStmt($this->factory->use(WeavedInterface::class))
            ->addStmt($this->factory->use(ReflectiveMethodInvocation::class)->as('Invocation'))
            ->addStmts($source->use)
            ->addStmt($classStm)
            ->getNode();

        return new Code(array_merge($source->declare, [$stmt]));
    }

    /**
     * Return "declare()" and "use" statement code
     *
     * @param \ReflectionClass<object> $class
     */
    private function getVisitorCode(\ReflectionClass $class) : CodeVisitor
    {
        $traverser = new NodeTraverser();
        $visitor = new CodeVisitor();
        $traverser->addVisitor($visitor);
        $fileName = $class->getFileName();
        if (is_bool($fileName)) {
            throw new InvalidSourceClassException(get_class($class));
        }
        $file = file_get_contents($fileName);
        if ($file === false) {
            throw new \RuntimeException($fileName); // @codeCoverageIgnore
        }
        $stmts = $this->parser->parse($file);
        if (is_array($stmts)) {
            $traverser->traverse($stmts);
        }

        return $visitor;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function getClassAnnotation(\ReflectionClass $class) : string
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);

        return serialize($classAnnotations);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return Property[]
     */
    private function getAopProps(\ReflectionClass $class) : array
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
     * @param \ReflectionClass<object> $class
     */
    private function getMethodAnnotations(\ReflectionClass $class) : string
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
     * @return null|string
     */
    private function getNamespace(CodeVisitor $source)
    {
        $parts = $source->namespace->name->parts ?? [];
        $ns = implode('\\', $parts);

        return $ns ? $ns : null;
    }
}
