<?php
/**
 * Ray
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
 */
class Bind extends \ArrayObject
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
    public function bindInterceptors($method, array $interceptors)
    {
        $this[$method] = $interceptors;
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
    public function bindMatcher(\Closure $matcher, array $interceptors)
    {
        $this->matchers[] = array($matcher, $interceptors);
        return $this;
    }

    /**
     * Get matched method name
     *
     * @param string  $name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name)
    {
        foreach ($this->matchers as $matcheInceptor) {
            list($matcher, $interceptors) = $matcheInceptor;
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
