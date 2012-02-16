<?php
/**
 * Ray
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
 */
final class Bind extends \ArrayObject
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
     *
     * @return Bind
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null)
    {
        if (! isset($this[$method])) {
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
     * Return has define any binding.
     *
     * @return bool
     */
    public function hasBinding()
    {
        $hasImplicitBinding = (count($this)) ? true : false;
        return $hasImplicitBinding;
    }

    /**
     * Get matched Interceptor
     *
     * @param string  $name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name)
    {
        // pre compiled inplicit matcher
        $interceptors = isset($this[$name]) ? $this[$name] : false;
        return $interceptors;
    }

    /**
     * Make pointcuts to binding information
     *
     * @param string $class
     * @param array  $pointcuts
     *
     * @return \Ray\Aop\Bind
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /* @var $pointcut Ray\Aop\Pointcut */
            $classMatcher = $pointcut->classMatcher;
            $isClassMatch = $classMatcher($class, Matcher::TARGET_CLASS);
            if ($isClassMatch === true) {
                $method = ($pointcut->methodMatcher->isAnnotateBinding()) ? 'bindByAnnoateBindig' : 'bindByCallable';
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
     */
    private function bindByCallable($class, Matcher $methodMatcher, array $interceptors)
    {
        $methods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);
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
    private function bindByAnnoateBindig($class, Matcher $methodMatcher, array $interceptors)
    {
        $matches = $methodMatcher($class, Matcher::TARGET_METHOD);
        if (! $matches) {
            return;
        }
        foreach ($matches as $matched) {
            if ($matched instanceof Matched) {
                $this->bindInterceptors($matched->methodName, $interceptors, $matched->annotation);
            }
        }
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
        $result = [];
        foreach ($this as $method => $interceptors) {
            $classNames = array_map(
                    function($interceptor){
                        return get_class($interceptor);
                    },
                    $interceptors
            );
            $intercetorsList = implode(',', $classNames);
            $result[] = "method[{$method}]=>intercept[{$intercetorsList}]";
        }
        $result = implode(' ', $result);
        return $result;
    }
}
