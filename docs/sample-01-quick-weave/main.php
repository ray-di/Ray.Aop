<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Compiler;
use Ray\Aop\Bind;

$bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);

$compiler = require dirname(dirname(__DIR__)) . '/scripts/instance.php';
$billingService = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);

try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
