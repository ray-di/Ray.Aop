<?php
namespace Ray\Aop\Sample;

use Ray\Aop\Pointcut,
    Ray\Aop\Matcher,
    Ray\Aop\Weaver,
    Ray\Aop\Bind;

require dirname(__DIR__) . '/bootstrap.php';

use Doctrine\Common\Annotations\AnnotationReader as Reader;

$bind = new Bind;
$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith('Ray\Aop\Sample\Annotation\WeekendBlock'), $interceptors);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$weavedBilling = new Weaver(new RealBillingService, $bind);
try {
    echo $weavedBilling->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
