<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;

class CodeGenVisitor extends NodeVisitorAbstract
{
    /**
     * @var Use_[]
     */
    private $use = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
            $this->use[] = $node;
        }
    }

    /**
     * @return Node\Stmt\Use_[]
     */
    public function __invoke()
    {
        return $this->use;
    }
}
