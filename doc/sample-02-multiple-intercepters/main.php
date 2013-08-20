<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;

$bind = (new Bind)->bindInterceptors('chargeOrder', array(new Timer, new intercepterA, new intercepterB));
$billingService = (new Compiler)->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);

try {
    $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
