<?php

namespace Ray\Aop\Mock;

interface AddInterface
{
    public function add(Num $num1, $num2);
}

interface SquareInterface
{
    public function square($num);
}

class Num
{
    public $value = 0;
}

class Foo implements AddInterface, SquareInterface
{
    public function add(Num $num1, $num2)
    {
        return $num1->value + $num2;
    }

    public function square($num = 1)
    {
        return $num * $num;
    }
}

class Foo_weaved
{

    public $object;
    private $bind;

    public function set($object, Bind $bind)
    {
        $this->object = $object;
        $this->bind = $bind;
    }

    public function add(Num $num1, $num2)
    {
        // direct call
        if (!isset($this->bind[__FUNCTION__])) {
            return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
        }

        // interceptor weaved call
        $interceptors = $this->bind[__FUNCTION__];
        $annotation = (isset($this->bind->annotation[__FUNCTION__])) ? $this->bind->annotation[__FUNCTION__] : null;
        /** @noinspection PhpParamsInspection */
        $invocation = new ReflectiveMethodInvocation(
            [$this->object,__FUNCTION__],
            func_get_args(),
            $interceptors,
            $annotation
        );

        return $invocation->proceed();
    }
}
