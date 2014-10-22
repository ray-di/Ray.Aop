<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\AbstractMatcher;

final class IsAny
{
    /**
     * @var array
     */
    private $builtinMethods = [
        'offsetExists',
        'offsetGet',
        'offsetSet',
        'offsetUnset',
        'append',
        'getArrayCopy',
        'count',
        'getFlags',
        'setFlags',
        'asort',
        'ksort',
        'uasort',
        'uksort',
        'natsort',
        'natcasesort',
        'unserialize',
        'serialize',
        'getIterator',
        'exchangeArray',
        'setIteratorClass',
        'getIterator',
        'getIteratorClass'
    ];

    /**
     * @param string $name
     * @param string $target
     *
     * @return bool
     */
    public function __invoke($name, $target)
    {
        if ($name instanceof \ReflectionMethod) {
            $name = $name->name;
        }
        if ($target === AbstractMatcher::TARGET_CLASS) {
            return true;
        }
        if (substr($name, 0, 2) === '__') {
            return false;
        }
        $isMatch = in_array($name, $this->builtinMethods) ? false : true;

        return $isMatch;
    }
}
