<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Matcher;

use Ray\Aop\AbstractMatcher;

final class AnyMatcher extends AbstractMatcher
{
    /**
     * @var array
     */
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
            self::$builtinMethods[] = $method->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        unset($arguments);

        return ! ($this->isMagicMethod($method->name) || $this->isBuiltinMethod($method->name));
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
