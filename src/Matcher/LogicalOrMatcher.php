<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

class LogicalOrMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        foreach ($arguments as $matcher) {
            /* @var $matcher AbstractMatcher */
            $isMatch =  $matcher->matchesClass($class, []);
            if ($isMatch === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        foreach ($arguments as $matcher) {
            /* @var $matcher AbstractMatcher */
            $isMatch = $matcher->matchesMethod($method, $matcher->getArguments());
            if ($isMatch === true) {
                return true;
            }
        }

        return false;
    }
}
