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
use function class_exists;
use function strpos;
use function strrchr;
use function substr;

final class AopClass
{
    /** @var CodeGenMethod */
    private $codeGenMethod;

    /** @var AopClassName */
    private $aopClassName;

    /** @var Node */
    private $traitStmt;

    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->aopClassName = $aopClassName;
        $this->codeGenMethod = new CodeGenMethod($parser, $factory);
        $this->traitStmt = $factory->useTrait('\Ray\Aop\InterceptTrait')->getNode();
    }

    /**
     * {@inheritdoc}
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function __invoke(CodeVisitor $visitor, ReflectionClass $sourceClass, BindInterface $bind): Class_
    {
        assert($visitor->class instanceof Class_);
        $methods = $this->codeGenMethod->getMethods($sourceClass, $bind, $visitor);
        $classStm = $visitor->class;
        assert(class_exists($sourceClass->name));
        $newClassName = ($this->aopClassName)($sourceClass->name, (string) $bind);
        $shortClassName = strpos($newClassName, '\\') === false ? $newClassName : substr((string) strrchr($newClassName, '\\'), 1);
        $classStm->name = new Identifier($shortClassName);
        $classStm->extends = new Name('\\' . $sourceClass->name);
        $classStm->implements[] = new Name('\Ray\Aop\WeavedInterface');
        /** @var list<Stmt> $stmts */
        $stmts = array_merge([$this->traitStmt], $methods);
        $classStm->stmts = $stmts;

        return $classStm;
    }
}
