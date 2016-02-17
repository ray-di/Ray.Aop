<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Builder\Class_ as Builder;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

final class CodeGen implements CodeGenInterface
{
    /**
     * @var \PHPParser\Parser
     */
    private $parser;

    /**
     * @var \PHPParser\BuilderFactory
     */
    private $factory;

    /**
     * @var \PHPParser\PrettyPrinter\Standard
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
     * @param \PHPParser\Parser                 $parser
     * @param \PHPParser\BuilderFactory         $factory
     * @param \PHPParser\PrettyPrinter\Standard $printer
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

    /**
     * @param string           $class
     * @param \ReflectionClass $sourceClass
     *
     * @return string
     */
    public function generate($class, \ReflectionClass $sourceClass, BindInterface $bind)
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

    /**
     * @param \ReflectionClass $class
     *
     * @return string
     */
    private function getUseStatements(\ReflectionClass $class)
    {
        $traverser = new NodeTraverser();
        $useStmtsVisitor = new CodeGenVisitor();
        $traverser->addVisitor($useStmtsVisitor);
        // parse
        $stmts = $this->parser->parse(file_get_contents($class->getFileName()));
        // traverse
        $traverser->traverse($stmts);
        // pretty print
        $code = $this->printer->prettyPrint($useStmtsVisitor());

        return (string) $code;
    }

    /**
     * Return class statement
     *
     * @param string           $newClassName
     * @param \ReflectionClass $class
     *
     * @return \PhpParser\Builder\Class_
     */
    private function getClass($newClassName, \ReflectionClass $class)
    {
        $parentClass = $class->name;
        $builder = $this->factory
            ->class($newClassName)
            ->extend($parentClass)
            ->implement('Ray\Aop\WeavedInterface');
        $builder = $this->addInterceptorProp($builder, $class);
        $builder = $this->addSerialisedAnnotationProp($builder, $class);

        return $builder;
    }

    /**
     * Add class doc comment
     *
     * @param Class_           $node
     * @param \ReflectionClass $class
     *
     * @return \PHPParser\Node\Stmt\Class_
     */
    private function addClassDocComment(Class_ $node, \ReflectionClass $class)
    {
        $docComment = $class->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new Doc($docComment)]);
        }

        return $node;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getClassAnnotation(\ReflectionClass $class)
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);

        return serialize($classAnnotations);
    }

    /**
     * @param Builder          $builder
     * @param \ReflectionClass $class
     *
     * @return Builder
     */
    private function addInterceptorProp(Builder $builder, \ReflectionClass $class)
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
     *
     * @param Builder          $builder
     * @param \ReflectionClass $class
     *
     * @return Builder
     */
    private function addSerialisedAnnotationProp(Builder $builder, \ReflectionClass $class)
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

    /**
     * @param \ReflectionClass $class
     *
     * @return string
     */
    private function getMethodAnnotations(\ReflectionClass $class)
    {
        $methodsAnnotation = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if (! $annotations) {
                continue;
            }
            $methodsAnnotation[$method->getName()] = $annotations;
        }

        return serialize($methodsAnnotation);
    }
}
