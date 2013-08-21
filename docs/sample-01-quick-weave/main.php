<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Compiler;
use Ray\Aop\Bind;

$bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);

$billingService = (new Compiler)->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);
try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
