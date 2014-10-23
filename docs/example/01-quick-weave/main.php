<?php

namespace Ray\Aop;

use Ray\Aop\Sample\WeekendBlocker;

require dirname(__DIR__) . '/bootstrap.php';

$compiler = new Compiler($_ENV['TMP_DIR']);
$billingService = $compiler->newInstance(
    'Ray\Aop\Sample\RealBillingService',
    [],
    (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker])
);

try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
