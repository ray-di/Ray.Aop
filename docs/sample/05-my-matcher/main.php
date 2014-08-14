<?php
namespace Ray\Aop;

use Ray\Aop\Sample\WeekendBlocker;
use Ray\Aop\Sample\MyMatcher;

require dirname(__DIR__) . '/bootstrap.php';

$matcher = new Matcher;
$myMatcher = new MyMatcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
    $matcher->any(),
    $myMatcher->contains('charge'),
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
