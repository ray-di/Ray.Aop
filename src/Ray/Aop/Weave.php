<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Weaver
 *
 * The proxy object to call intercepted method.
 *
 * @package Ray.Aop
 */
interface Weave
{
    /**
     * Get target object
     *
     * @return object
     */
    public function ___getObject();

    /**
     * Get target object
     *
     * @return Bind
     */
    public function ___getBind();

    /**
     * The magic method to call intercepted method.
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     * @throws \BadFunctionCallException
     */
    public function  __call($method, array $params);

    /**
     * Invoke with callable parameter.
     *
     * @param Callable $getParams
     * @param string   $method
     * @param array    $query
     *
     * @return mixed
     */
    public function __invoke(Callable $getParams, $method, array $query);
}
