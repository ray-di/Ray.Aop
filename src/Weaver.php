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
    protected $object;

    protected $interceptors;

    /**
     * Constractor
     *
     * @param object $object
     * @param Inpterceptor[]
     */
    public function __construct($object, array $interceptors)
    {
        $this->object = $object;
        $this->interceptors = $interceptors;
    }

    /**
     * The magic method to call intercepted method.
     *
     * @param string $name
     * @param array  $args
     */
    public function  __call($name, $args)
    {
        $invocation = new ReflectiveMethodInvocation(array($this->object, $name), $args, $this->interceptors);
        return $invocation->proceed();
    }
}