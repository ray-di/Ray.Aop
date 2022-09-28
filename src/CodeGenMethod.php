<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Parser;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use ReflectionMethod;

use function array_keys;
use function in_array;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
final class CodeGenMethod
{
    /** @var VisitorFactory */
    private $visitorFactory;

    /** @var CallIntercept */
    private $callIntercept;

    public function __construct(
        Parser $parser,
        BuilderFactory $factory
    ) {
        $this->visitorFactory = new VisitorFactory($parser);
        $this->callIntercept = new CallIntercept($factory);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return array<ClassMethod|Nop>
     */
    public function getMethods(ReflectionClass $reflectionClass, BindInterface $bind, CodeVisitor $code): array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $isBindingMethod = in_array($methodName, $bindingMethods, true);
            if ($isBindingMethod) {
                $classMethod = $this->getClassMethod($reflectionClass, $reflectionMethod, $code);
                // replace statements in the method
                $classMethod->stmts = $this->callIntercept->getStmts($classMethod->getReturnType());
                $methods[] = new Nop();
                $methods[] = $classMethod;
            }
        }

        return $methods;
    }

    /** @param ReflectionClass<object> $sourceClass */
    private function getClassMethod(
        ReflectionClass $sourceClass,
        ReflectionMethod $bindingMethod,
        CodeVisitor $code
    ): ClassMethod {
        $bindingMethodName = $bindingMethod->getName();
        foreach ($code->classMethod as $classMethod) {
            if ($classMethod->name->name === $bindingMethodName) {
                return $classMethod;
            }
        }

        $parentClass = $sourceClass->getParentClass();
        if ($parentClass === false) {
            throw new InvalidSourceClassException($sourceClass->getName()); // @codeCoverageIgnore
        }

        $code = ($this->visitorFactory)($parentClass);

        return $this->getClassMethod($parentClass, $bindingMethod, $code);
    }
}
