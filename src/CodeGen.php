<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use ReflectionClass;

use function array_merge;
use function assert;
use function implode;

final class CodeGen implements CodeGenInterface
{
    /** @var BuilderFactory */
    private $factory;

    /** @var CodeGenMethod */
    private $codeGenMethod;

    /** @var AopClassName */
    private $aopClassName;

    /** @var VisitorFactory */
    private $visitoryFactory;

    /** @var AopProps */
    private $aopProps;

    public function __construct(
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->factory = $factory;
        $this->codeGenMethod = new CodeGenMethod($parser);
        $this->aopClassName = $aopClassName;
        $this->visitoryFactory = new VisitorFactory($parser);
        $this->aopProps = new AopProps($factory);
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
        $propStms = ($this->aopProps)($sourceClass);
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
     * @return string|null
     */
    private function getNamespace(CodeVisitor $source)
    {
        $parts = $source->namespace->name->parts ?? [];
        $ns = implode('\\', $parts);

        return $ns ? $ns : null;
    }
}
