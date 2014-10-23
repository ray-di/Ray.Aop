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
        $args = array_merge([$class, $target], $this->args);

        $matcherClass = __NAMESPACE__ . '\Match\Is' . ucwords($this->method);
        $matched = call_user_func_array(new $matcherClass, $args);

        return $matched;
    }
}
