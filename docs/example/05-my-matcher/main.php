<?php
namespace Ray\Aop;

use Ray\Aop\Sample\WeekendBlocker;
use Ray\Aop\Sample\MyMatcher;
use Ray\Aop\Sample\AnnotationRealBillingService;
use Ray\Aop\Sample\RealBillingService;


require dirname(__DIR__) . '/bootstrap.php';

$matcher = new Matcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
    $matcher->any(),
    (new MyMatcher)->contains('charge'),
    $interceptors
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
