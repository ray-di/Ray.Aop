<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ReflectionClass;
use ReflectionMethod;

final class Bind implements BindInterface
{
    /**
     * @var array
     */
    public $bindings = [];

    /**
     * {@inheritdoc}
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /** @var $pointcut Pointcut */
            $this->bindPointcut($class, $pointcut);
        }

        return $this->bindings;
    }

    /**
     * @param string    $class
     * @param Pointcut  $pointcut
     */
    private function bindPointcut($class, Pointcut $pointcut)
    {
        $classMatcher = $pointcut->classMatcher;
        $isClassMatch = $classMatcher($class, Target::IS_CLASS);
        if ($isClassMatch === false) {

            return;
        }
        $isAnnotateBinding = method_exists($pointcut->methodMatcher, 'isAnnotateBinding') && $pointcut->methodMatcher->isAnnotateBinding();
        if ($isAnnotateBinding) {
            $this->bindByAnnotateBinding($class, $pointcut->methodMatcher, $pointcut->interceptors);

            return;
        }
        $this->methodMatchBind($class, $pointcut->methodMatcher, $pointcut->interceptors);

    }
    /**
     * {@inheritdoc}
     */
    public function bindInterceptors($method, array $interceptors)
    {
        $this->bindings[$method] = !isset($this->bindings[$method]) ? $interceptors : array_merge($this->bindings[$method], $interceptors);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($name)
    {
        // pre compiled implicit matcher
        $interceptors = isset($this->bindings[$name]) ? $this->bindings[$name] : false;

        return $interceptors;
    }

    /**
     * Bind interceptor by callable matcher matching
     *
     * @param string          $class
     * @param AbstractMatcher $methodMatcher
     * @param Interceptor[]   $interceptors
     */
    private function methodMatchBind($class, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $methods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $isMethodMatch = ($methodMatcher($method, Matcher::TARGET_METHOD) === true);
            if ($isMethodMatch) {
                $this->bindInterceptors($method->name, $interceptors);
            }
        }
    }

    /**
     * Bind interceptor by annotation binding
     *
     * @param string          $class
     * @param AbstractMatcher $methodMatcher
     * @param Interceptor[]   $interceptors
     */
    private function bindByAnnotateBinding($class, AbstractMatcher $methodMatcher, array $interceptors)
    {
        $matches = (array)$methodMatcher($class, Matcher::TARGET_METHOD);
        if (!$matches) {
            return;
        }
        foreach ($matches as $matched) {
            if ($matched instanceof Matched) {
                $this->bindInterceptors($matched->methodName, $interceptors, $matched->annotation);
            }
        }
    }

    public function __toString()
    {
        return md5(serialize($this->bindings));
    }
}
