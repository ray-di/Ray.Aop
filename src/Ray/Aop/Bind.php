<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
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
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /** @var $pointcut Pointcut */
            $classMatcher = $pointcut->classMatcher;
            $isClassMatch = $classMatcher($class, Matcher::TARGET_CLASS);
            if ($isClassMatch !== true) {
                continue;
            }
            if ($pointcut->methodMatcher->isAnnotateBinding()) {
                $this->bindByAnnotateBinding($class, $pointcut->methodMatcher, $pointcut->interceptors);
                continue;
            }
            $this->bindByCallable($class, $pointcut->methodMatcher, $pointcut->interceptors);
        }

        return $this;
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
     * Bind interceptor by callable matcher
     *
     * @param string  $class
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     */
    private function bindByCallable($class, Matcher $methodMatcher, array $interceptors)
    {
        $methods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $isMethodMatch = ($methodMatcher($method->name, Matcher::TARGET_METHOD) === true);
            if ($isMethodMatch) {
                $this->bindInterceptors($method->name, $interceptors);
            }
        }
    }

    /**
     * Bind interceptor by annotation binding
     *
     * @param string  $class
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     */
    private function bindByAnnotateBinding($class, Matcher $methodMatcher, array $interceptors)
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
