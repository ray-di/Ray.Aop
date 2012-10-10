<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Ray\Aop\Exception\UndefinedProperty;
use ArrayAccess;
use RuntimeException;
use BadFunctionCallException;

/**
 * Weaver
 *
 * The proxy object to call intercepted method.
 *
 * @package Ray.Aop
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
     * Constructor
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
     * (non-PHPDoc)
     * @see \Ray\Aop\Weave::___getObject()
     */
    public function ___getObject()
    {
        return $this->object;
    }

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\Weave::___getBind()
     */
    public function ___getBind()
    {
        return $this->bind;
    }

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\Weave::__call()
     */
    public function  __call($method, array $params)
    {
        if (!method_exists($this->object, $method)) {
            throw new BadFunctionCallException($method);
        }
        // direct call
        if (!isset($this->bind[$method])) {
            return call_user_func_array(array($this->object, $method), $params);
        }
        // interceptor weaved call
        $interceptors = $this->bind[$method];
        $annotation = (isset($this->bind->annotation[$method])) ? $this->bind->annotation[$method] : null;
        /** @noinspection PhpParamsInspection */
        $invocation = new ReflectiveMethodInvocation(
            [
                $this->object,
                $method
            ],
            $params,
            $interceptors,
            $annotation
        );

        return $invocation->proceed();
    }

    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\Weave::__call()
     */
    public function __invoke(Callable $getParams, $method, array $query)
    {
        return $this->__call($method, $getParams($this->object, $method, $query));
    }

    /**
     * Return public property
     *
     * @param string $name
     *
     * @throws UndefinedProperty
     */
    public function __get($name)
    {
        if (isset($this->object->$name)) {
            return $this->object->$name;
        }
        throw new UndefinedProperty(__METHOD__ . ':' . get_class($this->object) . '::$' . $name);
    }

    /**
     * Set public property
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->object->$name = $value;
    }

    /**
     * Return string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->object;
    }

    /**
     * Return offsetExists
     *
     * @param mixed $offset
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function offsetExists($offset)
    {
        if (!$this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }

        return isset($this->object[$offset]);
    }

    /**
     * Return offset exists
     *
     * @param mixed $offset
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function offsetGet($offset)
    {
        if (!$this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }

        return $this->object[$offset];
    }

    /**
     * Set
     *
     * @param string $offset key
     * @param mixed  $value
     *
     * @throws RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->object instanceof ArrayAccess) {
            throw new RuntimeException('ArrayAccess not allowed.');
        }
        $this->object[$offset] = $value;
    }

    /**
     * Unset
     *
     * @param string $offset key
     */
    public function offsetUnset($offset)
    {
        unset($this->object[$offset]);
    }
}
