<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver,
    Ray\Aop\Bind;

$bind = new Bind;
$bind->bindInterceptors('chargeOrder', array(new Timer, new intercepterA, new intercepterB));
$weavedBilling = new Weaver(new RealBillingService, $bind);
try {
    $weavedBilling->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
