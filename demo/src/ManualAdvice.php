<?php
namespace Ray\Aop\Demo;

class ManualAdvice
{
    public function before()
    {
        echo "before A\n";
    }

    public function after()
    {
        echo "after A\n";
    }
}
