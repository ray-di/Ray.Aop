<?php

namespace Ray\Aop\Sample;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;
use Ray\Aop\MatchInterface;

class MyMatcher implements MatchInterface
{
    public function contains($contain)
    {
        return new Matcher(__FUNCTION__, func_get_args(), __CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        list($contain) = $args;

        return (strpos($name, $contain) !== false);
    }
}
