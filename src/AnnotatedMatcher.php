<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

final class AnnotatedMatcher extends BuiltinMatcher
{
    public $annotation;

    public function __construct(string $matcherName, array $arguments)
    {
        parent::__construct($matcherName, $arguments);
        $this->annotation = $arguments[0];
    }
}
