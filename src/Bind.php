<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ArrayObject;
use ReflectionClass;
use ReflectionMethod;

final class Bind extends ArrayObject implements BindInterface
{
    /**
     * Annotated binding annotation
     *
     * @var array [$method => $annotations]
     */
    public $annotation = [];

    /**
     * {@inheritdoc}
     */
    public function hasBinding()
    {
        $hasImplicitBinding = (count($this)) ? true : false;

        return $hasImplicitBinding;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /** @var $pointcut Pointcut */
            $this->bindPointcut($class, $pointcut);
        }

        return $this;
    }

    /**
     * @param string    $class
     * @param Pointcut $pointcut
     *
     * @return void
     */
    private function bindPointcut($class, Pointcut $pointcut)
    {
        $classMatcher = $pointcut->classMatcher;
        $isClassMatch = $classMatcher($class, Matcher::TARGET_CLASS);
        if ($isClassMatch !== true) {
            return;
        }
        if (method_exists($pointcut->methodMatcher, 'isAnnotateBinding') && $pointcut->methodMatcher->isAnnotateBinding()) {
            $this->bindByAnnotateBinding($class, $pointcut->methodMatcher, $pointcut->interceptors);
            return;
        }
        $this->methodMatchBind($class, $pointcut->methodMatcher, $pointcut->interceptors);

    }
    /**
     * {@inheritdoc}
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null)
    {
        $this[$method] = !isset($this[$method]) ? $interceptors : array_merge($this[$method], $interceptors);
        if ($annotation) {
            $this->annotation[$method] = $annotation;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($name)
    {
        // pre compiled implicit matcher
        $interceptors = isset($this[$name]) ? $this[$name] : false;

        return $interceptors;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $binds = [];
        foreach ($this as $method => $interceptors) {
            $inspectorsInfo = [];
            foreach ($interceptors as $interceptor) {
                $inspectorsInfo[] .= get_class($interceptor);
            }
            $inspectorsInfo = implode(',', $inspectorsInfo);
            $binds[] = "{$method} => " . $inspectorsInfo;
        }
        $result = implode(',', $binds);

        return $result;
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
}
