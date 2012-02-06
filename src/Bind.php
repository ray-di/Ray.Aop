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
