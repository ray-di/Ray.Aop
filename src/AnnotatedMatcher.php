<?php

declare(strict_types=1);

namespace Ray\Aop;

final class AnnotatedMatcher extends BuiltinMatcher
{
    /**
     * @var string
     */
    public $annotation;

    /**
     * @param array<int, string> $arguments
     */
    public function __construct(string $matcherName, array $arguments)
    {
        parent::__construct($matcherName, $arguments);
        $this->annotation = $arguments[0];
    }
}
