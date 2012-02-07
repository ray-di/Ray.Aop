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
     * Matcher
     *
     * @var array
     */
    private $matchers = array();

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
        $this[$method] = $interceptors;
        $this->annotation[$method] = $annotation;
        return $this;
    }

    /**
     * Bind matcher
     *
     * @param \Closure $matcher
     * @param array    $interceptors
     *
     * @return Bind
     */
    public function bindMatcher(Callable $matcher, array $interceptors)
    {
        $this->matchers[] = array($matcher, $interceptors);
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
        $hasMatcher =  (count($this->matchers)) ? true : false;
        $hasBinding = $hasImplicitBinding || $hasMatcher;
        return $hasBinding;
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
        foreach($this as $methodName => $interceptors) {
            if ($name === $methodName) {
                return $interceptors;
            }
        }
        // runtime matcher
        foreach ($this->matchers as $matcheInterceptor) {
            list($matcher, $interceptors) = $matcheInterceptor;
            $matched = $matcher($name);
            if ($matched === true) {
                return $interceptors;
            }
        }
        return false;
    }

    /**
     * Make pointcuts to binding information
     *
     * @param string       $class
     * @param \ArrayObject $pointcuts
     *
     * @return \Ray\Aop\Bind
     */
    public function bind($class, \ArrayObject $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            list($classMatcher, $methodMatcher, $interceptors) = $pointcut;
            if ($classMatcher($class, Matcher::TARGET_CLASS) !== true) {
                continue;
            }
            // compiled by annotation binding matcher
            if ($methodMatcher instanceof Matcher){
                goto METHOD_MATCH_BY_ANNOTATE_BINDING;
            }
METHOD_MATCH_BY_CALLABLE:
            $methods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($methodMatcher($method->name, Matcher::TARGET_METHOD) === true) {
                    $this->bindInterceptors($method->name, $interceptors);
                }
            }
            continue;

METHOD_MATCH_BY_ANNOTATE_BINDING:
            $matches = $methodMatcher($class,  Matcher::TARGET_METHOD);
            if (! $matches) {
                continue;
            }
            foreach ($matches as $matched) {
                if ($matched instanceof Matched) {
                    $this->bindInterceptors($matched->methodName, $interceptors, $matched->annotation);
                }
            }
        }
        return $this;
    }

    /**
     * to String
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        foreach ($this as $method => $interceptors) {
            $classNames = array_map(
            function($interceptor){
                return get_class($interceptor);
            },
            $interceptors
            );
            $intercetorsList = implode(',', $classNames);
            $result .= "[{$method}]=>[{$intercetorsList}]\n";
        }
        return $result;
    }
}
