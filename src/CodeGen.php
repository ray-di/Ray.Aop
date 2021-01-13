<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Parser;
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
        Parser $parser,
        BuilderFactory $factory,
        AopClassName $aopClassName
    ) {
        $this->factory = $factory;
        $this->visitoryFactory = new VisitorFactory($parser);
        $this->aopClass = new AopClass($parser, $factory, $aopClassName);
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
