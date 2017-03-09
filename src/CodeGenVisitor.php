<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class CodeGenVisitor extends NodeVisitorAbstract
{
    /**
     * @var Use_[]
     */
    private $use = [];

    /**
     * @return Node\Stmt\Use_[]
     */
    public function __invoke()
    {
        return $this->use;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
            $this->use[] = $node; // @codeCoverageIgnore
        }
    }
}
