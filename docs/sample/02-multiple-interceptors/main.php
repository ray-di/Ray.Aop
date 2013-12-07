<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;

$bind = (new Bind)->bindInterceptors('chargeOrder', array(new Timer, new interceptorA, new interceptorB));
$compiler = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';

$billingService = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);

try {
    $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
