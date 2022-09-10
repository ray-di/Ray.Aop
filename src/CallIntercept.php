<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeAbstract;

final class CallIntercept
{
    /**
     * $this->_intercept(func_get_args(), __FUNCTION__);
     *
     * @var Expression
     */
    private $expression;

    /**
     * return $this->_intercept(func_get_args(), __FUNCTION__);
     *
     * @var Return_
     */
    private $return;

    public function __construct(BuilderFactory $factory)
    {
        $methodCall = $factory->methodCall(
            $factory->var('this'),
            '_intercept',
            [$factory->funcCall('func_get_args'), $factory->constFetch('__FUNCTION__')]
        );
        $this->expression = new Expression($methodCall);
        $this->return = new Return_($methodCall);
    }

    /**
     * @return list<Expression>|list<Return_>
     */
    public function getStmts(?NodeAbstract $returnType): array
    {
        $isVoid = $returnType instanceof Identifier && ($returnType->name === 'void' || $returnType->name === 'never');

        return $isVoid ? [$this->expression] : [$this->return];
    }
}
