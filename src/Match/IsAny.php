<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Match;

use Ray\Aop\AbstractMatcher;
use Ray\Aop\MatchInterface;
use Ray\Aop\Target;

final class IsAny implements MatchInterface
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
            self::$builtinMethods[] =  $method->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($name, $target, array $args)
    {
        unset($args);
        if ($name instanceof \ReflectionMethod) {
            $name = $name->getName();
        }

        if ($target === Target::IS_METHOD && $this->isInvalidMethod($name)) {
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
