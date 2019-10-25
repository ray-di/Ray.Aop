<?php

declare(strict_types=1);

namespace Ray\Aop;

use function array_merge;
use Doctrine\Common\Annotations\AnnotationReader;
use function implode;
use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
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
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printer;

    /**
     * @var CodeGenMethod
     */
    private $codeGenMethod;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        Standard $printer
    ) {
        $this->parser = $parser;
        $this->factory = $factory;
        $this->printer = $printer;
        $this->codeGenMethod = new CodeGenMethod($parser, $factory, $printer);
        $this->reader = new AnnotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(\ReflectionClass $sourceClass, BindInterface $bind) : Code
    {
        $source = $this->getVisitorCode($sourceClass);
        assert($source->class instanceof Class_);
        $methods = $this->codeGenMethod->getMethods($sourceClass, $bind, $source);
        $propStms = $this->getAopProps($sourceClass);
        $classStm = $source->class;
        $newClassName = sprintf('%s_%s', (string) $source->class->name, $bind->toString(''));
        $classStm->name = new Identifier($newClassName);
        $classStm->extends = new Name('\\' . $sourceClass->name);
        $classStm->implements[] = new Name('WeavedInterface');
        $classStm->stmts = array_merge($propStms, $methods);
        $ns = $this->getNamespace($source);
        $stmt = $this->factory->namespace($ns)
            ->addStmt($this->factory->use('Ray\Aop\WeavedInterface'))
            ->addStmt($this->factory->use('Ray\Aop\ReflectiveMethodInvocation')->as('Invocation'))
            ->addStmts($source->use)
            ->addStmt($classStm)
            ->getNode();
        $code = new Code;
        $code->code = $this->printer->prettyPrintFile(array_merge($source->declare, [$stmt]));

        return $code;
    }

    /**
     * Return "declare()" and "use" statement code
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

    private function getClassAnnotation(\ReflectionClass $class) : string
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);

        return serialize($classAnnotations);
    }

    /**
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
