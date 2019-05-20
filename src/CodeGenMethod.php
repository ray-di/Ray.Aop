<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

final class CodeGenMethod
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
    }

    public function getMethods(\ReflectionClass $class, BindInterface $bind, CodeVisitor $code) : array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $classMethods = $code->classMethod;
        $methods = [];
        foreach ($classMethods as $classMethod) {
            $methodName = $classMethod->name->name;
            $method = new \ReflectionMethod($class->name, $methodName);
            $isBindingMethod = in_array($methodName, $bindingMethods, true);
            /* @var $method \ReflectionMethod */
            $isPublic = $classMethod->flags === Class_::MODIFIER_PUBLIC;
            if ($isBindingMethod && $isPublic) {
                $methodInsideStatements = $this->getMethodInsideStatement($method);
                // replace statements in the method
                $classMethod->stmts = $methodInsideStatements;
                $methods[] = $classMethod;
            }
        }

        return $methods;
    }

    /**
     * @return Node\Stmt[]
     */
    private function getMethodInsideStatement(\ReflectionMethod $method) : array
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new AopTemplateConverter($method));
        $stmts = $this->getTemplateMethodNodeStmts();

        // traverse
        $stmts = $traverser->traverse($stmts);
        $result = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt) {
                $result[] = $stmt;
            }
        }

        return $result;
    }

    private function getTemplateMethodNodeStmts() : array
    {
        $code = $this->getTemplateCode();
        $node = $this->parser->parse($code)[0];
        if (! $node instanceof Class_) {
            throw new \LogicException; // @codeCoverageIgnore
        }
        $methodNode = $node->getMethods()[0];
        if ($methodNode->stmts === null) {
            throw new \LogicException; // @codeCoverageIgnore
        }

        return $methodNode->stmts;
    }

    /**
     * Return CodeGenTemplate string
     *
     * Compiler takes only the statements in the method. Then create new inherit code with interceptors.
     *
     * @see http://paul-m-jones.com/archives/182
     * @see http://stackoverflow.com/questions/8343399/calling-a-function-with-explicit-parameters-vs-call-user-func-array
     * @see http://stackoverflow.com/questions/1796100/what-is-faster-many-ifs-or-else-if
     * @see http://stackoverflow.com/questions/2401478/why-is-faster-than-in-php
     */
    private function getTemplateCode() : string
    {
        return <<<'EOT'
<?php
class AopTemplate extends \Ray\Aop\FakeMock implements Ray\Aop\WeavedInterface
{
    /**
     * @var array
     *
     * [$methodName => [$interceptorA[]][]
     */
    public $bindings;

    /**
     * @var bool
     */
    private $isAspect = true;

    /**
     * Method Template
     *
     * @param mixed $a
     */
    public function templateMethod($a, $b)
    {
        if (! $this->isAspect) {
            $this->isAspect = true;

            return parent::templateMethod($a, $b);
        }

        $this->isAspect = false;
        $result = (new Invocation($this, __FUNCTION__, [$a, $b], $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;

        return $result;
    }
}
EOT;
    }
}
