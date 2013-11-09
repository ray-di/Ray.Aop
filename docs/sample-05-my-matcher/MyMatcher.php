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
    public function contain($contain)
    {
        $this->method = __FUNCTION__;
        $this->args = $contain;

        return clone $this;

    }

    /**
     * Return isContain
     *
     * @param $name    method name
     * @param $target  \Ray\Aop\AbstractMatcher::TARGET_CLASS | \Ray\Aop\AbstractMatcher::Target_METHOD
     * @param $contain
     *
     * @return bool
     */
    protected function isContain($name, $target, $contain)
    {
        unset($target);
        $result = (strpos($name, $contain) !== false);

        return $result;
    }
}