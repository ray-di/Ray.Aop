<?php

declare(strict_types=1);

namespace Ray\Aop\Matcher;

use ArrayObject;
use Override;
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Types;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function in_array;
use function str_starts_with;

/**
 * @psalm-import-type Arguments from Types
 * @psalm-import-type BuiltinMethodsNames from Types
 */
final class AnyMatcher extends AbstractMatcher
{
    /** @var BuiltinMethodsNames|null */
    private static $builtinMethods = null;

    public function __construct()
    {
        parent::__construct();

        self::$builtinMethods ??= $this->getBuiltinMethods();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesClass(ReflectionClass $class, array $arguments): bool
    {
        unset($class, $arguments);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function matchesMethod(ReflectionMethod $method, array $arguments): bool
    {
        unset($arguments);

        return ! ($this->isMagicMethod($method->name) || $this->isBuiltinMethod($method->name));
    }

    /** @return BuiltinMethodsNames */
    private function getBuiltinMethods(): array
    {
        $methods = (new ReflectionClass(ArrayObject::class))->getMethods();
        $builtinMethods = [];
        foreach ($methods as $method) {
            $builtinMethods[] = $method->name;
        }

        return $builtinMethods;
    }

    /** @psalm-pure */
    private function isMagicMethod(string $name): bool
    {
        return str_starts_with($name, '__');
    }

    /** @psalm-external-mutation-free */
    private function isBuiltinMethod(string $name): bool
    {
        assert(self::$builtinMethods !== null);

        return in_array($name, self::$builtinMethods, true);
    }
}
