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
    private static $builtinMethods = [];

    public function __construct()
    {
        if (! self::$builtinMethods) {
            $this->setBuildInMethods();
        }
    }

    private function setBuildInMethods()
    {
        $methods = (new \ReflectionClass('\ArrayObject'))->getMethods();
        foreach ($methods as $method) {
            self::$builtinMethods[] =  $method->getName();
        }
    }

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

        if ($target === AbstractMatcher::TARGET_METHOD && $this->isInvalidMethod($name)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isInvalidMethod($name)
    {
        return $this->isMagicMethod($name) || $this->isBuiltinMethod($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isMagicMethod($name)
    {
        return substr($name, 0, 2) === '__';
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isBuiltinMethod($name)
    {
        $isBuiltin = in_array($name, self::$builtinMethods);

        return $isBuiltin;
    }
}
