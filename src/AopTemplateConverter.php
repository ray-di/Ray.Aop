<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;

final class AopTemplateConverter extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var Arg[]
     */
    private $args = [];

    private $proceedArg;

    public function __construct(\ReflectionMethod $method)
    {
        $this->method = $method->name;
        $proceedArg = [];
        foreach ($method->getParameters() as $parameter) {
            $this->args[] = new Arg(new Variable($parameter->name));
            $proceedArg[] = new ArrayItem(new Variable($parameter->name));
        }
        $this->proceedArg = new Arg(new Node\Expr\Array_($proceedArg));
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof StaticCall && $node->name instanceof Identifier && $node->name->name === 'templateMethod') {
            $node->name = $this->method;
            $node->args = $this->args;

            return $node;
        }

        return $this->updateReflectiveMethodInvocation2ndParam($node);
    }

    private function updateReflectiveMethodInvocation2ndParam(Node $node) : Node
    {
        if ($node instanceof MethodCall && $node->name instanceof Identifier && $node->name->name === 'proceed') {
            $node->var->args[2] = $this->proceedArg;

            return $node;
        }

        return $node;
    }
}
