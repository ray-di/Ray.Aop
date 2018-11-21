<?php

declare(strict_types=1);

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
