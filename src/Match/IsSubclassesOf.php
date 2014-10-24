<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\Exception\InvalidArgument;
use Ray\Aop\MatchInterface;
use Ray\Aop\Target;

class IsSubclassesOf implements MatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($class, $target, array $args)
    {
        list($superClass) = $args;
        $this->validation($class, $target);
        if (! class_exists($class) || ! class_exists($superClass)) {
            return false;
        }
        $isSubClass = (new \ReflectionClass($class))->isSubclassOf($superClass) || ($class === $superClass);

        return $isSubClass;
    }

    /**
     * @param string $class
     * @param bool   $target
     */
    private function validation($class, $target)
    {
        if ($class instanceof \ReflectionMethod) {
            throw new InvalidArgument($class->name);
        }
        if ($target === Target::IS_METHOD) {
            throw new InvalidArgument($class);
        }
    }
}
