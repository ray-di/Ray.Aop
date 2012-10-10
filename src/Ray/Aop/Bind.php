<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ReflectionClass;
use ReflectionMethod;
use ArrayObject;

/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 */
final class Bind extends ArrayObject implements BindInterface
{
    /**
     * Annotated binding annotation
     *
     * @var array [$method => $annotations]
     */
    public $annotation = [];

    /**
     * Bind method to interceptors
     *
     * @param string $method
     * @param array  $interceptors
     * @param object $annotation   Binding annotation if annotate bind
     *
     * @return Bind
     */

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::bindInterceptors()
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null)
    {
        if (!isset($this[$method])) {
            $this[$method] = $interceptors;
        } else {
            $this[$method] = array_merge($this[$method], $interceptors);
        }
        if ($annotation) {
            $this->annotation[$method] = $annotation;
        }

        return $this;
    }

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::hasBinding()
     */
    public function hasBinding()
    {
        $hasImplicitBinding = (count($this)) ? true : false;

        return $hasImplicitBinding;
    }

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::bind()
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /** @var $pointcut Pointcut */
            $classMatcher = $pointcut->classMatcher;
            $isClassMatch = $classMatcher($class, Matcher::TARGET_CLASS);
            if ($isClassMatch === true) {
                $method = ($pointcut->methodMatcher->isAnnotateBinding()) ? 'bindByAnnotateBinding' : 'bindByCallable';
                $this->$method($class, $pointcut->methodMatcher, $pointcut->interceptors);
            }
        }

        return $this;
    }

    /**
     * Bind interceptor by callable matcher
     *
     * @param string  $class
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     * @noinspection PhpUnusedPrivateMethodInspection
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
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function bindByAnnotateBinding($class, Matcher $methodMatcher, array $interceptors)
    {
        $matches = (array) $methodMatcher($class, Matcher::TARGET_METHOD);
        if (!$matches) {
            return;
        }
        foreach ($matches as $matched) {
            if ($matched instanceof Matched) {
                $this->bindInterceptors($matched->methodName, $interceptors, $matched->annotation);
            }
        }
    }

    /**
     * Get matched Interceptor
     *
     * @param string $name class name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name)
    {
        // pre compiled implicit matcher
        $interceptors = isset($this[$name]) ? $this[$name] : false;

        return $interceptors;
    }

    /**
     * to String
     *
     * for logging
     *
     * @return string
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
}
