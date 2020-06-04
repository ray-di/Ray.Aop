<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

require __DIR__ . '/bootstrap.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

$start = microtime(true);
$max = 1000;
cache_write:
    $compiler = new Compiler(__DIR__ . '/tmp');
    for ($i = 0; $i < $max; $i++) {
        $pointcut = new Pointcut(
            (new Matcher)->any(),
            (new Matcher)->annotatedWith(WeekendBlock::class),
            [new WeekendBlocker]
        );
        $bind = (new Bind)->bind(AnnotationRealBillingService::class, [$pointcut]);
        $compiler->newInstance(AnnotationRealBillingService::class, [], $bind);
    }

$time1 = microtime(true) - $start;
file_put_contents(__DIR__ . '/.cache', serialize([$bind, $time1]));
