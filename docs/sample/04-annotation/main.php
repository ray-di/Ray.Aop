<?php

namespace Ray\Aop;

use Ray\Aop\Sample\WeekendBlocker;

require dirname(__DIR__) . '/bootstrap.php';

use Doctrine\Common\Annotations\AnnotationReader as Reader;

$matcher = new Matcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
    $matcher->any(),
    $matcher->annotatedWith('Ray\Aop\Sample\Annotation\WeekendBlock'),
    $interceptors
);
$bind = (new Bind)->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);
$compiler = new Compiler(sys_get_temp_dir());
$billingService = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);
try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
