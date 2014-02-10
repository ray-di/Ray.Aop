<?php
namespace Ray\Aop\Sample;

use Ray\Aop\Pointcut;
use Ray\Aop\Matcher;
use Ray\Aop\Bind;

require dirname(__DIR__) . '/bootstrap.php';

use Doctrine\Common\Annotations\AnnotationReader as Reader;

$matcher = new Matcher(new Reader);
$myMatcher = new MyMatcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
    $matcher->any(),
    $myMatcher->contains('charge'),
    $interceptors
);
$bind = (new Bind)->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);
$compiler = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
$billingService = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);
try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
