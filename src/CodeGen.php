<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use ReflectionClass;

use function array_merge;
use function implode;

final class CodeGen implements CodeGenInterface
{
    /** @var BuilderFactory */
    private $factory;

    /** @var VisitorFactory */
    private $visitoryFactory;

    /** @var AopClass  */
    private $aopClass;

    public function __construct(
        BuilderFactory $factory,
        VisitorFactory $visitorFactory,
        AopClass $aopClass
    ) {
        $this->factory = $factory;
        $this->visitoryFactory = $visitorFactory;
        $this->aopClass = $aopClass;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReflectionClass<object> $sourceClass
     */
    public function generate(ReflectionClass $sourceClass, BindInterface $bind): Code
    {
        $visitor = ($this->visitoryFactory)($sourceClass);
        $classStm = ($this->aopClass)($visitor, $sourceClass, $bind);
        $ns = $this->getNamespace($visitor);
        $stmt = $this->factory->namespace($ns)
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
