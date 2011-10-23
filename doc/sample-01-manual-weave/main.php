<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver,
    Ray\Aop\Bind;

$bind = new Bind;
$bind->bindInterceptors('chargeOrder', array(new WeekendBlocker));

$weavedBilling = new Weaver(new RealBillingService, $bind);
try {
    echo $weavedBilling->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
