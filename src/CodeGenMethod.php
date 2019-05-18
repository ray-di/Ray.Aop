<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Ray\Aop\Annotation\AbstractAssisted;

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

    private $reader;

    /**
     * @var AbstractAssisted
     */
    private $assisted;

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
        $this->reader = new AnnotationReader;
    }

    public function getMethods(\ReflectionClass $class, BindInterface $bind) : array
    {
        $bindingMethods = array_keys($bind->getBindings());
        $stmts = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->assisted = $this->reader->getMethodAnnotation($method, AbstractAssisted::class);
            $isBindingMethod = in_array($method->name, $bindingMethods, true);
            /* @var $method \ReflectionMethod */
            if ($isBindingMethod && $method->isPublic()) {
                $stmts[] = $this->getMethod($method);
            }
        }

        return $stmts;
    }

    /**
     * Return method statement
     *
     * @return \PhpParser\Node\Stmt\ClassMethod
     */
    private function getMethod(\ReflectionMethod $method)
    {
        $methodStmt = $this->factory->method($method->name);
        $params = $method->getParameters();
        foreach ($params as $param) {
            $methodStmt = $this->getMethodStatement($param, $methodStmt);
        }
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionType) {
            $this->setReturnType($returnType, $methodStmt);
        }
        $methodInsideStatements = $this->getMethodInsideStatement($method);
        $methodStmt->addStmts($methodInsideStatements);

        return $this->addMethodDocComment($methodStmt, $method);
    }

    /**
     * Return parameter reflection
     */
    private function getMethodStatement(\ReflectionParameter $param, Method $methodStmt) : Method
    {
        /* @var $paramStmt Param */
        $paramStmt = $this->factory->param($param->name);
        /* @var $param \ReflectionParameter */
        $this->setParameterType($param, $paramStmt);
        $this->setDefault($param, $paramStmt);
        $methodStmt->addParam($paramStmt);

        return $methodStmt;
    }

    private function addMethodDocComment(Method $methodStmt, \ReflectionMethod $method) : ClassMethod
    {
        $node = $methodStmt->getNode();
        $docComment = $method->getDocComment();
        if ($docComment) {
            $node->setAttribute('comments', [new Doc($docComment)]);
        }

        return $node;
    }

    /**
     * @return \PhpParser\Node[]
     */
    private function getMethodInsideStatement(\ReflectionMethod $method) : array
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new AopTemplateConverter($method));
        $stmts = $this->getTemplateMethodNodeStmts();

        // traverse
        return $traverser->traverse($stmts);
    }

    private function setDefault(\ReflectionParameter $param, Param $paramStmt)
    {
        if ($param->isDefaultValueAvailable()) {
            $paramStmt->setDefault($param->getDefaultValue());

            return;
        }
        if ($this->assisted instanceof AbstractAssisted && in_array($param->name, $this->assisted->values, true)) {
            $paramStmt->setDefault(null);
        }
    }

    private function setParameterType(\ReflectionParameter $param, Param $paramStmt)
    {
        $type = $param->getType();
        if ($type == null) {
            return;
        }
        if ($param->isVariadic()) {
            $paramStmt->makeVariadic();
        }
        $paramString = (string) $param;
        $isNullableType = is_int(strpos($paramString, '<required>')) && is_int(strpos($paramString, 'or NULL'));
        $destType = $isNullableType ? new NullableType((string) $type) : (string) $type;
        $paramStmt->setTypeHint($destType);
    }

    private function setReturnType(\ReflectionType $returnType, Method $methodStmt)
    {
        $type = $returnType->allowsNull() ? new NullableType((string) $returnType) : (string) $returnType;
        $methodStmt->setReturnType($type);
    }

    /**
     * @return Node[]
     */
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
        if ($this->isAspect === false) {
            $this->isAspect = true;

            return parent::templateMethod($a, $b);
        }

        $this->isAspect = false;
        // invoke interceptor
        $result = (new \Ray\Aop\ReflectiveMethodInvocation($this, __FUNCTION__, [$a, $b], $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;

        return $result;
    }
}
EOT;
    }
}
