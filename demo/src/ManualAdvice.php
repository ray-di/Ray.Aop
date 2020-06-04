<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

class ManualAdvice
{
    public function before()
    {
        echo 'before A' . PHP_EOL;
    }

    public function after()
    {
        echo 'after A' . PHP_EOL;
    }
}
