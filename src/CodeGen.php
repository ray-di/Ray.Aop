<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
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
    }

    /**
     * @param string           $class
     * @param \ReflectionClass $sourceClass
     *
     * @return string
     */
    public function generate($class, \ReflectionClass $sourceClass)
    {
        $stmt = $this
            ->getClass($class, $sourceClass)
            ->addStmts($this->codeGenMethod->getMethods($sourceClass))
            ->getNode();
        $stmt = $this->addClassDocComment($stmt, $sourceClass);
        $code = $this->printer->prettyPrint([$stmt]);

        return $code;
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
            ->implement('Ray\Aop\WeavedInterface')
            ->addStmt(
                $this->factory->property('isIntercepting')->makePrivate()->setDefault(true)
            )->addStmt(
                $this->factory->property('bind')->makePublic()
            );

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
            $node->setAttribute('comments', [new \PHPParser\Comment\Doc($docComment)]);
        }

        return $node;
    }
}
