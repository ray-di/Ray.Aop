<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use Ray\Aop\Exception\InvalidSourceClassException;
use ReflectionClass;
use RuntimeException;

use function file_get_contents;
use function get_class;
use function is_array;
use function is_bool;

final class VisitorFactory
{
    /** @var Parser */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function __invoke(ReflectionClass $class): CodeVisitor
    {
        $traverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $visitor = new CodeVisitor();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($visitor);
        $fileName = $class->getFileName();
        if (is_bool($fileName)) {
            throw new InvalidSourceClassException(get_class($class));
        }

        $file = file_get_contents($fileName);
        if ($file === false) {
            throw new RuntimeException($fileName); // @codeCoverageIgnore
        }

        $stmts = $this->parser->parse($file);
        if (is_array($stmts)) {
            $traverser->traverse($stmts);
        }

        return $visitor;
    }
}
