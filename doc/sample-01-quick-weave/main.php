<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver,
    Ray\Aop\Bind;

$bind = new Bind;
$bind->bindInterceptors('chargeOrder', [new WeekendBlocker]);

$billingService = new Weaver(new RealBillingService, $bind);
try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
