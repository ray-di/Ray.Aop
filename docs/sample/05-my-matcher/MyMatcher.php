<?php

namespace Ray\Aop\Sample;

use Ray\Aop\AbstractMatcher;

class MyMatcher extends AbstractMatcher
{
    /**
     * @param $contain
     *
     * @return MyMatcher
     */
    public function contains($contain)
    {
        $this->createMatcher(__FUNCTION__, $contain);

        return clone $this;

    }

    /**
     * Return isContain
     *
     * @param string $name    class or method name
     * @param string $target  \Ray\Aop\AbstractMatcher::TARGET_CLASS | \Ray\Aop\AbstractMatcher::Target_METHOD
     * @param string $contain
     *
     * @return bool
     */
    protected function isContains($name, $target, $contain)
    {
        unset($target);
        $result = (strpos($name, $contain) !== false);

        return $result;
    }
}