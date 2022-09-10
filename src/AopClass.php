<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use ReflectionClass;

use function array_merge;
use function assert;

final class AopClass
{
    /** @var CodeGenMethod */
    private $codeGenMethod;

    /** @var AopClassName */
    private $aopClassName;

    /** @var AopProps */
    private $aopProps;

    /** @var Node */
    private $traitStmt;

    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->aopClassName = $aopClassName;
        $this->codeGenMethod = new CodeGenMethod($parser);
        $this->aopProps = new AopProps($factory);
        $this->traitStmt = $factory->useTrait('\Ray\Aop\AopTrait')->getNode();
    }

    /**
     * {@inheritdoc}
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function __invoke(CodeVisitor $visitor, ReflectionClass $sourceClass, BindInterface $bind): Class_
    {
        assert($visitor->class instanceof Class_);
        $methods = $this->codeGenMethod->getMethods($bind, $visitor);
        $propStms = ($this->aopProps)($sourceClass);
        $classStm = $visitor->class;
        $newClassName = ($this->aopClassName)((string) $visitor->class->name, $bind->toString(''));
        $classStm->name = new Identifier($newClassName);
        $classStm->extends = new Name('\\' . $sourceClass->name);
        $classStm->implements[] = new Name('WeavedInterface');
        /** @var list<Stmt> $stmts */
        $stmts = array_merge($propStms, [$this->traitStmt], $methods);
        $classStm->stmts = $stmts;

        return $classStm;
    }
}
