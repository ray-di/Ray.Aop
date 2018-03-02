<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class CodeGenVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node\Stmt[]
     */
    private $selectedNodes = [];

    /**
     * @return Node\Stmt[]
     */
    public function __invoke() : array
    {
        return $this->selectedNodes;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Use_ || $node instanceof Declare_) {
            $this->selectedNodes[] = $node; // @codeCoverageIgnore
        }
    }
}
