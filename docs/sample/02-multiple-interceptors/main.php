<?php

namespace Ray\Aop;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Sample\Timer;
use Ray\Aop\Sample\interceptorA;
use Ray\Aop\Sample\interceptorB;

$bind = (new Bind)->bindInterceptors('chargeOrder', array(new Timer, new interceptorA, new interceptorB));
$compiler = new Compiler($_ENV['TMP_DIR']);

$billingService = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);

try {
    $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
