<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\DuplicatedNamedParam;
use Ray\Aop\MethodInvocation;

class NamedArgs implements NamedArgsInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        $params = $invocation->getMethod()->getParameters();
        $i = 0;
        $namedArgs = [];
        foreach ($params as $param) {
            if (isset($namedArgs[$param->name])) {
                throw new DuplicatedNamedParam($param->name);
            }
            $namedArgs[$param->name] = $args[$i++];
        }

        return $namedArgs;
    }
}
