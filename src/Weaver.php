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
    protected $bind;


    protected $interceptors;
    /**
     * @var \ReflectionClass
     */
    protected $reflcetion;

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
        $this->reflcetion = new \ReflectionClass($object);
    }

    /**
     * The magic method to call intercepted method.
     *
     * @param string $name
     * @param array  $args
     */
    public function  __call($name, $args)
    {
        if ($this->reflcetion->hasMethod($name) === false) {
            throw new \BadFunctionCallException($name);
        }
        // explicit bind
        if (isset($this->bind[$name])) {
            $interceptors = $this->bind[$name];
            goto weave;
        }
        // matcher bind
        $bind = $this->bind;
        $interceptors = $bind($name);
        if ($interceptors !== false) {
            goto weave;
        }
        // no binding
        return call_user_func_array(array($this->object, $name), $args);
weave:
        $invocation = new ReflectiveMethodInvocation(array($this->object, $name), $args, $interceptors);
        return $invocation->proceed();
    }
}
