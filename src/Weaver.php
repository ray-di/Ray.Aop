<?php
/**
 * Ray
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Weaver
 *
 * The proxy object to call intercepted method.
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
 */
class Weaver implements Weave
{
    /**
     * Target object
     *
     * @var mixed
     */
    protected $object;

    /**
     * Interceptor binding
     *
     * @var array
     */
    protected $bind;

    /**
     * Interceptors
     *
     * @var array
     */
    protected $interceptors;

    /**
     * Constractor
     *
     * @param object $object
     * @param Inpterceptor[]
     */
    public function __construct($object, Bind $bind)
    {
        $this->object = $object;
        $this->bind = $bind;
    }

    /**
     * The magic method to call intercepted method.
     *
     * @param string $method
     * @param array  $params
     */
    public function  __call($method, $params)
    {
        if (!method_exists($this->object, $method)) {
            throw new \BadFunctionCallException($method);
        }
        // explicit bind
        if (isset($this->bind[$method])) {
            $interceptors = $this->bind[$method];
            goto weave;
        }
        // matcher bind
        $bind = $this->bind;
        $interceptors = $bind($method);
        if ($interceptors !== false) {
            goto weave;
        }
        // no binding
        return call_user_func_array(array($this->object, $method), $params);
weave:
        $invocation = new ReflectiveMethodInvocation(array($this->object, $method), $params, $interceptors);
        return $invocation->proceed();
    }

    /**
     * Invoke with callable parameter.
     *
     * @param Callable $getParams
     * @param string   $method
     * @param query    $query
     * @return mixed
     */
    public function __invoke($getParams, $method, $query)
    //public function __invoke(Callable $getParams, $method, $query) // for PHP 5.4
    {
        return $this->__call($method, $getParams($this->object, $method, $query));
    }
}
