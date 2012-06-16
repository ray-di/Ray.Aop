<?php
/**
 * Ray
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\UndefinedProperty;
use ArrayAccess;
use RuntimeException;

/**
 * Weaver
 *
 * The proxy object to call intercepted method.
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
 */
class Weaver implements Weave, ArrayAccess
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
     * @param Bind   $bind
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
     *
     * @return mixed
     * @throws \BadFunctionCallException
     */
    public function  __call($method, array $params)
    {
        if (!method_exists($this->object, $method)) {
            throw new \BadFunctionCallException($method);
        }
        // direct call
        if (! isset($this->bind[$method])) {
            return call_user_func_array(array($this->object, $method), $params);
        }
        // interceptor weaved call
        $interceptors = $this->bind[$method];
        $annotation = (isset($this->bind->annotation[$method])) ? $this->bind->annotation[$method] : null;
        $invocation = new ReflectiveMethodInvocation(
                array($this->object, $method),
                $params,
                $interceptors,
                $annotation
        );
        return $invocation->proceed();
    }

    /**
     * Invoke with callable parameter.
     *
     * @param mixed  $getParams Callable
     * @param string $method
     * @param array  $query
     *
     * @return mixed
     */
    public function __invoke($getParams, $method, array $query)
    {
        return $this->__call($method, $getParams($this->object, $method, $query));
    }

    /**
     * Return parameter
     *
     * @param string $name
     */
    public function __get($name)
    {
        if (isset($this->object->$name)) {
            return $this->object->$name;
        }
        throw new UndefinedProperty(get_class($this->object) . '::$' . $name);
    }

    /**
     * Return string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->object;
    }

    /**
     * Return offsetExists
     *
     * @param string $name key
     */
    public function offsetExists($offset)
    {
        if (! $this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }
        return isset($this->object[$offset]);
    }

    /**
     * Return offset exists
     *
     * @param string $name key
     */
    public function offsetGet($offset)
    {
        if (! $this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }
        return $this->object[$offset];
    }

    /**
     * Set
     *
     * @param string $name key
     */
    public function offsetSet($offset, $value)
    {
        if (! $this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }
        $this->object[$offset] = $value;
    }

    /**
     * Unset
     *
     * @param string $name key
     */
    public function offsetUnset($offset)
    {
        unset($this->object[$offset]);
    }

    /**
     * Get target object
     *
     * @return \Ray\Aop\mixed
     */
    public function ___getObject()
    {
        return $this->object;
    }

    /**
     * Get target object
     *
     * @return \Ray\Aop\mixed
     */
    public function ___getBind()
    {
        return $this->bind;
    }
}
