<?php

namespace Ray\Aop;

use Ray\Aop\Sample\WeekendBlocker;
use Ray\Aop\Sample\Annotation\WeekendBlock;
use Ray\Aop\Sample\AnnotationRealBillingService;
use Ray\Aop\Sample\RealBillingService;

require dirname(__DIR__) . '/bootstrap.php';

$matcher = new Matcher;
$pointcut = new Pointcut(
    $matcher->any(),
    $matcher->annotatedWith(WeekendBlock::class),
    [new WeekendBlocker]
);
$bind = new Bind;
$bind->bind(AnnotationRealBillingService::class, [$pointcut]);
$compiler = new Compiler($_ENV['TMP_DIR']);
$billingService = $compiler->newInstance(RealBillingService::class, [], $bind);

try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
