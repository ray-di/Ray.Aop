<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use PhpParser\Builder\Class_ as Builder;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Ray\Aop\Php71\BindInterface;
use Ray\Aop\Php71\CodeGenInterface;

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
     * @var IndexedReader
     */
    private $reader;

    /**
     * @param \PhpParser\Parser                 $parser
     * @param \PhpParser\BuilderFactory         $factory
     * @param \PhpParser\PrettyPrinter\Standard $printer
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
        $this->reader = new IndexedReader(new AnnotationReader);
    }

    public function generate(string $class, \ReflectionClass $sourceClass, BindInterface $bind) : string
    {
        $methods = $this->codeGenMethod->getMethods($sourceClass, $bind);
        $stmt = $this
            ->getClass($class, $sourceClass)
            ->addStmts($methods)
            ->getNode();
        $stmt = $this->addClassDocComment($stmt, $sourceClass);
        $code = $this->printer->prettyPrint([$stmt]);
        $statements = $this->getUseStatements($sourceClass);

        return $statements . $code;
    }

    private function getUseStatements(\ReflectionClass $class) : string
    {
        $traverser = new NodeTraverser();
        $useStmtsVisitor = new CodeGenVisitor();
        $traverser->addVisitor($useStmtsVisitor);
        // parse
        $stmts = $this->parser->parse(file_get_contents($class->getFileName()));
        /* @var $stmts array */
        // traverse
        $traverser->traverse($stmts);
        // pretty print
        $code = $this->printer->prettyPrint($useStmtsVisitor());

        return (string) $code;
    }

    /**
     * Return class statement
     */
    private function getClass(string $newClassName, \ReflectionClass $class) : Builder
    {
        $parentClass = $class->name;
        $builder = $this->factory
            ->class($newClassName)
            ->extend($parentClass)
            ->implement('Ray\Aop\WeavedInterface');
        $builder = $this->addInterceptorProp($builder);
        $builder = $this->addSerialisedAnnotationProp($builder, $class);

        return $builder;
    }

    /**
     * Add class doc comment
     */
    private function addClassDocComment(Class_ $node, \ReflectionClass $class) : Class_
    {
        $docComment = $class->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new Doc($docComment)]);
        }

        return $node;
    }

    private function getClassAnnotation(\ReflectionClass $class) : string
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);

        return serialize($classAnnotations);
    }

    private function addInterceptorProp(Builder $builder) : Builder
    {
        $builder->addStmt(
            $this->factory
                ->property('isIntercepting')
                ->makePrivate()
                ->setDefault(true)
        )->addStmt(
            $this->factory->property('bind')
            ->makePublic()
        );

        return $builder;
    }

    /**
     * Add serialised
     */
    private function addSerialisedAnnotationProp(Builder $builder, \ReflectionClass $class) : Builder
    {
        $builder->addStmt(
            $this->factory
                ->property('methodAnnotations')
                ->setDefault($this->getMethodAnnotations($class))
                ->makePublic()
        )->addStmt(
            $this->factory
                ->property('classAnnotations')
                ->setDefault($this->getClassAnnotation($class))
                ->makePublic()
        );

        return $builder;
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
}
