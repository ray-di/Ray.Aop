<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

class IsStartsWith
{
    public function __invoke($name, $target, $startsWith)
    {
        unset($target);
        if ($name instanceof \ReflectionMethod) {
            $name = $name->name;
        }
        $result = (strpos($name, $startsWith) === 0) ? true : false;

        return $result;
    }
}
