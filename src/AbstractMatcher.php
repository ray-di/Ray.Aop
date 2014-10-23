<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

abstract class AbstractMatcher
{
    /**
     * Match CLASS
     *
     * @var bool
     */
    const TARGET_CLASS = true;

    /**
     * Match Method
     *
     * @var bool
     */
    const TARGET_METHOD = false;

    /**
     * Lazy match method
     *
     * @var string
     */
    protected $method;

    /**
     * Lazy match args
     *
     * @var array
     */
    protected $args;


    /**
     * @param string $method
     */
    public function __construct($method = null, $args = null)
    {
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target)
    {
        $matcherClass = __NAMESPACE__ . '\Match\Is' . ucwords($this->method);
        $matched = call_user_func(new $matcherClass, $class, $target, $this->args);

        return $matched;
    }

    /**
     * Return isAnnotateBinding
     *
     * @return bool
     */
    public function isAnnotateBinding()
    {
        return $this->method === 'annotatedWith';
    }

    /**
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, array $arguments)
    {
        $matcher = (new \ReflectionClass('Is' . ucwords($name)))->newInstance();
        $matched = call_user_func_array($matcher, $arguments);

        return $matched;
    }
}
