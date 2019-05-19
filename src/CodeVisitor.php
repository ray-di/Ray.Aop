<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

final class CodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Declare_[]
     */
    public $declare;

    /**
     * @var Use_[]
     */
    public $use;

    public function enterNode(Node $node)
    {
        if ($node instanceof Declare_) {
            $this->declare[] = $node;
        }
        if ($node instanceof Use_) {
            $this->use[] = $node;
        }
    }
}
